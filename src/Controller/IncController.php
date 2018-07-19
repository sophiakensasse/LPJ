<?php
//src/Controller/IncController.php
namespace App\Controller; //on est dans le dossier src, mais on ecrit App. C'est son nom pour l'appeler (c'est par rapport à l'autolaod)

//creer le lien avec Twig
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// filtrer les admin et les user, avec isGranted
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use Symfony\Component\HttpFoundation\Response; //retourner une reponse au format HTML

use Symfony\Component\Routing\Annotation\Route; //pour utiliser les annotation installé via le sension/extra-bundle (voir config symphony)
//il y a eu une creation de fichier dans config/routes/annotations.yaml


class IncController extends Controller
{
    public function navbar(AuthorizationCheckerInterface $authChecker)
    {
        //si l'utilisateur est loggué et possede les droits ROLE_ADMIN
        if($authChecker->isGranted('ROLE_ADMIN'))
        {
            $liens = array(
                array('href' => 'gestionMembre', 'libelle_lien' => ' Gestion Membre'),
                array('href' => 'gestionSalles', 'libelle_lien' => 'Gestion Salles'),
                array('href' => 'tableauDeBord', 'libelle_lien' => 'Tableau de bord'),                
                array('href' => 'concept', 'libelle_lien' => 'Concept'),               
                array('href' => 'services', 'libelle_lien' => 'services'),
                array('href' => 'salle', 'libelle_lien' => 'Creation de site'),
                array('href' => 'aide', 'libelle_lien' => 'Aide'),                
                array('href' => 'nos_refs', 'libelle_lien' => 'Nos refs'),
                array('href' => 'contact', 'libelle_lien' => 'Contact'),
                array('href' => 'deconnexion', 'libelle_lien' => 'Deconnexion'));
        }
        
        //si l'utilisateur est loggué et possede les droits ROLE_USER
        elseif($authChecker->isGranted('ROLE_USER'))
        {
            $liens = array(
                array('href' => 'tableauDeBord', 'libelle_lien' => 'Tableau de bord'),                
                array('href' => 'concept', 'libelle_lien' => 'Concept'),               
                array('href' => 'services', 'libelle_lien' => 'services'),
                array('href' => 'salle', 'libelle_lien' => 'Creation de site'),
                array('href' => 'aide', 'libelle_lien' => 'Aide'),
                array('href' => 'nos_refs', 'libelle_lien' => 'Nos refs'),
                array('href' => 'contact', 'libelle_lien' => 'Contact'),
                array('href' => 'deconnexion', 'libelle_lien' => 'Deconnexion'));
        }
        //pour les autres
        else
        {
            $liens = array(                
                array('href' => 'concept', 'libelle_lien' => 'Concept'),                
                array('href' => 'services', 'libelle_lien' => 'services'),
                array('href' => 'salle', 'libelle_lien' => 'Creation de site'),
                array('href' => 'aide', 'libelle_lien' => 'Aide'),
                array('href' => 'nos_refs', 'libelle_lien' => 'Nos refs'),
                array('href' => 'contact', 'libelle_lien' => 'Contact'));
                
        }
        
        return $this->render('inc/navbar.html.twig', array('liens' =>$liens));
    }
    
    public function footer()
    {
        // faire apparaitre la date et l'heure
        $dte = date('d/m/Y H:i:s');
        //appel du template footer.html.twig
        return $this->render('inc/footer.html.twig', array('dte' => $dte));
    }
    
}