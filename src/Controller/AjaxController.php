<?php
namespace App\Controller;
//src/Controller/PublicController.php
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
//annotations pour les routes
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//pour les catégories
use App\Entity\Indisponible;
//pour les membres
use App\Entity\Membre;

// sécurité pour verifier que l'utilisateur est connecté pour pouvoir reserver
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

// table Salle pour récupérer l'id de la salle
use App\Entity\Salle;

//pour le calendrier statique
use App\Services\Calendrier;

//pour le calendrier ajout
use App\Services\CalendrierAjout;

class AjaxController extends Controller
{
	/**
	 * @Route("/suppPanier", name="suppPanier")
	 */
	public function suppPanier(Request $request)
	{
		$id = $request->get('id');
		$panier = $this->getDoctrine()->getRepository(Panier::class);
		$lignePanier = $panier->find($id);
		//Entity Manager
		$em = $this->getDoctrine()->getManager();
		$em->remove($lignePanier);
		$em->flush();
		return new Response(json_encode(['msg' => '<p class="alert alert-success">élément supprimé</p>']));
	} 

	/**
	 * @Route("/modifPanier", name="modifPanier")
	 */
	public function modifPanier(Request $request)
	{
		$id = $request->get('id');
		$value = $request->get('value');
		$em = $this->getDoctrine()->getManager();
		$panier = $em->getRepository(Panier::class)->find($id);
		//Entity Manager
		$panier->setQuantiteProduit($value);
		$em->flush();

		return new response(json_encode(['msg' => '<p class="alert alert-success">Quantité modifiée</p>']));
	}

	/**
	 * @Route("/recupAdr", name="recupAdr")
	 */
	public function recupAdr()
	{
		$retour = array();
		$em = $this->getDoctrine()->getManager();
		$adresses = $em->getRepository(Clients::class)->findAll();
		foreach($adresses as $adr)
		{
			$retour[] = ['adr' =>$adr->getAdresseClient().' '
													.$adr->getCpClient().' '
													.$adr->getVilleClient(), 
									 'nom' => $adr->getPrenomClient().' '
									 				 .$adr->getNomClient()];
		}
		return new Response(json_encode($retour));
	}
    
    /**
	* @Route(
	*	  "/reservation/{id}",
	*	  name="reservation",
    *     requirements={"id":"\d+"})
	*/
	public function reservation($id, Request $request, CalendrierAjout $calendrierAjout, AuthorizationCheckerInterface $authChecker)
	{
        //si l'utilisateur est loggué, donc securité
        if($authChecker->isGranted('ROLE_USER'))
        {
            //pour afficher la description du produit en rappel au dessus du formulaire
            $salle = $this->getDoctrine()->getRepository(Salle::class);
            //infos de la salle (SELECT * FROM salle WHERE id= :id)
            $detailSalle = $salle->find($id);

            //on appelle notre service qui va afficher le calendrier et qui est lié au IndisponibleRepository
            $calendrier_indisponible = $this->getDoctrine()->getRepository(Indisponible::class);
            $affichageCalendrier = $calendrierAjout->creationCalendrier($id, $calendrier_indisponible);

            //affichage du formulaire
            return $this->render('public/reservation.html.twig',
                                        array('affichage_calendrier' => $affichageCalendrier,'title' => 'reservation', 'detailSalle'=> $detailSalle, 'id' => $detailSalle->getId()));
        }else
        {
            //si il n'est pas connecté, on le ramene à la page connexion
            return $this->redirectToRoute('connexion');
        }
	}

    
    /**
	* @Route(
	*	  "/ajoutReservation",
	*	  name="ajoutReservation")
	*/
	public function ajoutReservation(Request $request)
	{
        
            //GETTERS AJAX
            $idSalle = $request->get('id');
            //value correspond à la date
            $date = $request->get('date');
        
            $salleAreserver = $this->getDoctrine()->getRepository(Salle::class);
            $idSalle = $salleAreserver->find($request->get('id'));
            $em = $this->getDoctrine()->getManager();
            //pour creer une nouvelle reservation, on creer un nouvel objet
            $jourReservation = new Indisponible();

            //SETTERS
            //On insere l'id membre
            $idMembre = $this->getUser();
            
            $jourReservation->setIdMembre($idMembre);
            //on insere l'id salle
            $jourReservation->setIdSalle($idSalle);
            //1 = "loué" et '2' = "indisponible(proprietaire)";
            $statutIndisponible = 1;
            $jourReservation->setStatutIndisponible($statutIndisponible);
            //setter pour le jour
            //$dateBonFormat =  $date->format('Y-m-d');
            
            //$dateOk = date('Y-m-d', strtotime($date));
            $dateOk = new \DateTime($date);
            //dump($dateBonFormat);
            $jourReservation->setJourIndisponible($dateOk);
        
        
        //enregistrement dans la table
        $em->persist($jourReservation);
        $em->flush();

		return new response(json_encode(['msg' => '<p class="alert alert-success">Jour reservé !</p>']));
		
	}

}