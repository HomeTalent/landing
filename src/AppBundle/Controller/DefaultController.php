<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use AppBundle\Entity\Prospect;

class DefaultController extends Controller
{

    const SERVICES_CHOICES = [
        'bricolage' => 'Bricolage',
        'jardinage' => 'Jardinage',
        'demenagement' => 'Déménagement',
        'menage-repassage' => 'Ménage/Repassage',
        'assemblage-meuble' => 'Assemblage meuble',
        'informatique' => 'Informatique',
        'coursier' => 'Coursier',
        'animaux' => 'Garde d\'animaux',
        'evenementiel' => 'Evénementiel',
        'prestation-administratif' => 'Administratif',
        'service-personne' => 'Service à la personne',
        'mode-beaute' => 'Mode, Beauté & Bien-être'
    ];

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $translator = $this->get('translator');
        $sent = false;

        $askerForm = $this->get('form.factory')
        ->createNamedBuilder('asker_form', FormType::class, ['type' => 'ASKER'])
        ->add('type', HiddenType::class)
        ->add('email', EmailType::class, [
                'label' => '',
                'attr' => ['placeholder' => 'mail@example.com']
            ]
        )
        ->add('services', ChoiceType::class, [
                'label' => $translator->trans('Mes besoins'),
                'choices' => self::SERVICES_CHOICES, 
                'multiple' => true, 
                'attr' => ['class' => 'selectpicker']
            ]
        )
        ->add('send', SubmitType::class, [
                'label' => $translator->trans('J\'AI BESOIN D\'AIDE !'), 
                'attr' => ['class' => 'save btn btn-primary']
            ]
        )->getForm();

        $askerForm->handleRequest($request);


        $taskerForm = $this->get('form.factory')
        ->createNamedBuilder('tasker_form', FormType::class, ['type' => 'TASKER'])
        ->add('type', HiddenType::class)
        ->add('email', EmailType::class, [
                'label' => '',
                'attr' => ['placeholder' => 'mail@example.com']
            ]
        )
        ->add('services', ChoiceType::class, [
                'label' => $translator->trans('Mes talents'),
                'choices' => self::SERVICES_CHOICES, 
                'multiple' => true, 
                'attr' => ['class' => 'selectpicker']
            ]
        )
        ->add('send', SubmitType::class, [
                'label' => $translator->trans('JE PROPOSE MES SERVICES !'), 
                'attr' => ['class' => 'save btn btn-primary']
            ]
        )->getForm();

        $taskerForm->handleRequest($request);

        if ($askerForm->isSubmitted() && $askerForm->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $askerForm->getData();
            $sent = true;
        }

        if ($taskerForm->isSubmitted() && $taskerForm->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $taskerForm->getData();
            $sent = true;
        }

        if($sent) {
            $prospect = new Prospect();
            $prospect->setEmail($data['email']);
            $prospect->setServices($data['services']);
            $prospect->setType($data['type']);

            $em = $this->getDoctrine()->getManager();
            // tells Doctrine you want to (eventually) save the Product (no queries yet)
            $em->persist($prospect);
            // actually executes the queries (i.e. the INSERT query)
            $em->flush();
        }

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'askerForm' => $askerForm->createView(),
            'taskerForm' => $taskerForm->createView(),
            'sent' => $sent,
        ]);
    }

    /**
    * @Route ("/setLang", name="set_language")
    */
    public function setLanguageAction(Request $request)
    {
        $locale = $request->get('language');
        $request->getSession()->set('_locale', $locale);

        return $this->redirect('/');
    }
}
