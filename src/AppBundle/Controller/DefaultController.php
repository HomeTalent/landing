<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use AppBundle\Entity\Prospect;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        list($sent, $contactForm) = $this->prepareIndex($request);

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'contact_form' => $contactForm->createView(),
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

    /**
     * @Route ("/flyer", name="flyer")
     */
    public function flyerAction()
    {
        return $this->redirect('/');
    }

    /**
     * @Route ("/asker", name="index_asker")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function askerAction(Request $request)
    {
        list($sent, $contactForm) = $this->prepareIndex($request);

        // replace this example code with whatever you need
        return $this->render('default/index_asker.html.twig', [
            'contact_form' => $contactForm->createView(),
            'sent' => $sent,
        ]);
    }

    /**
     * @Route ("/tasker", name="index_tasker")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function taskerAction(Request $request)
    {
        list($sent, $contactForm) = $this->prepareIndex($request);

        // replace this example code with whatever you need
        return $this->render('default/index_tasker.html.twig', [
            'contact_form' => $contactForm->createView(),
            'sent' => $sent,
        ]);
    }

    /**
     * @param Request $request
     * @return array
     */
    private function prepareIndex(Request $request)
    {
        $translator = $this->get('translator');
        $sent = false;

        $contactForm = $this->get('form.factory')
            ->createNamedBuilder('contact_form', FormType::class)
            ->add(
                'name',
                TextType::class,
                [
                    'label' => '',
                    'attr' => ['placeholder' => $translator->trans('Nom')]
                ]
            )
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => '',
                    'attr' => ['placeholder' => $translator->trans('Email')]
                ]
            )
            ->add(
                'message',
                TextareaType::class,
                [
                    'label' => '',
                    'attr' => ['placeholder' => 'Message']
                ]
            )
            ->add(
                'send',
                SubmitType::class,
                [
                    'label' => $translator->trans('ENVOYER'),
                ]
            )->getForm();

        $contactForm->handleRequest($request);

        if ($contactForm->isSubmitted() && $contactForm->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $contactForm->getData();
            $sent = true;
        }

        if ($sent) {
            $message = \Swift_Message::newInstance()
                ->setSubject('Contact enquiry from hometalent')
                ->setFrom($data['email'])
                ->setTo($this->container->getParameter('contact_email'))
                ->setBody($this->renderView('default/email.txt.twig', array('data' => $data)));
            $this->get('mailer')->send($message);
            $this->redirect('/');

            return array($sent, $contactForm);
        }

        return array($sent, $contactForm);
    }
}
