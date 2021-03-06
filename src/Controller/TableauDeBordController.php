<?php
//src/Controller/TableauDeBordController.php
namespace App\Controller; //on est dans le dossier src, mais on ecrit App. C'est son nom pour l'appeler (c'est par rapport à l'autolaod)

//creer le lien avec Twig
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request; // pour les requètes pour le formulaire
use Symfony\Component\HttpFoundation\Response; //retourner une reponse au format HTML

// sécurité
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


use Symfony\Component\Routing\Annotation\Route; //pour utiliser les annotation installé via le sension/extra-bundle (voir config symphony)

// table utilisateurs pour enregistrer un nouvel utilisateur
use App\Entity\Membre;

//formulaire inscription
use App\Form\MembreType;

//gerrer upload photo profil
use App\Services\UploadPhotoMembre;


class TableauDeBordController extends Controller
{
    public function navTableauDeBord(AuthorizationCheckerInterface $authChecker)
    {
        
        //si l'utilisateur est loggué 
        if($authChecker->isGranted('ROLE_USER'))
        {
            //recuperer le statut membre pour savoir quel menu on lui affiche
            // 1 -> Locataire
            // 2 -> Propriétaire / proprietaire
            $statutMembre = $this->getUser();
            //dump($statutMembre);
        }
        
        return $this->render('inc/navTableauDeBord.html.twig', array('statut' =>$statutMembre));
    }
    
    /**
	* @Route("/tableauDeBord",name="tableauDeBord")
	*/
	public function tableauDeBord()
	{
		//récupération de l'utilisateur connecté
		$membre = $this->getUser();
        
		return $this->render('security/tableauDeBord.html.twig', 
									array('title' => 'Tableau de bord',
												'membre' => $membre));
	}

        /**
    * @Route(
    *     "/modifierProfil",
    *     name="modifierProfil")
    */
    public function modifier(Request $request, UserPasswordEncoderInterface $passwordEncoder, UploadPhotoMembre $fileUploader)
    {
        //connection avec la table Membre
        $em = $this->getDoctrine()->getRepository(Membre::class);
        //récupération de l'utilisateur
        $membre = $em->find($this->getUser());
        //création du formulaire
        $form = $this->createForm(MembreType::class, $membre);

        //récupération des données du formulaire
        $form->handleRequest($request);
        
        //si soumis et validé
        if($form->isSubmitted() && $form->isValid())
        {
            //on recupere les information du formulaire
            /*$membre = $form->getData();*/ //??????????????????????????hnadleRequest?????
            
            // Upload photo //////////////////////////////////////
            
            $photoProfil = $membre->getPhotoMembre();
            $fileName = $fileUploader->upload($photoProfil);
            $membre->setPhotoMembre($fileName);

            ////Fin de uploadphoto/////////////////////////////////////////////////////
            
            
            //enregistrer la date du jour au format SQL pour enregistrer dans la table
            $dateEnregistrement = new \DateTime(); 
            $membre->setDateEnregistrementMembre($dateEnregistrement);
            
            //enregistrer en automatique des infos, c'est fictif, pour ne pas faire planter la requete sql
            $infosCarteBancaire = 'IBAN'; 
            $membre->setInfoBancaireMembre($infosCarteBancaire);
            
            
            //encodage du mot de passe
            $hash = $passwordEncoder->encodePassword($membre, $membre->getPasswordMembre());
            $membre->setPasswordMembre($hash);
            
            
            //enregistrement dans la table
            $em = $this->getDoctrine()->getManager();
            $em->persist($membre);
            $em->flush();
            
    
            //retour à la page profil
            return $this->redirectToRoute('tableauDeBord');
        }
        //affichage du formulaire
        return $this->render('security/modifierProfil.html.twig',
                                    array('form' => $form->createView(),
                                                'title' => 'modifier', 'membre' => $membre));
    }

        /*POUR LA PHOTO DE PROFIL*/
    
    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}