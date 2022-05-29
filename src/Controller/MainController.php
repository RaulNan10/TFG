<?php namespace App\Controller;

use App\Entity\Assessment;
use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\AssessmentType;
use App\Form\EventType;
use App\Form\Register2Type;
use App\Form\RegisterType;
use App\Form\UserType;


class MainController extends AbstractController {

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response {
        $error=$authenticationUtils->getLastAuthenticationError();
        $lastUsername=$authenticationUtils->getLastUsername();


        return $this->render('login/login.html.twig', [ 'controller_name'=> 'MainController',
            'error'=> $error,
            'last_username'=> $lastUsername,
            ]);
    }

    /**
     * @Route("/registro", name="app_registro")
     */
    public function registro(UserPasswordHasherInterface $passwordHasher, Request $request, EntityManagerInterface $em): Response {
        $user=new User();

        $form=$this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $a=['ROLE_USER'];
            $user->setRoles($a);

            $hashedPasword=$passwordHasher->hashPassword($user,
            $form->get('password')->getData());
            $user->setPassword($hashedPasword);

            $imagen = $form->get('image')->getData();
            $extension = $imagen->guessExtension();
            $nombreImagen = "user".time(). "." .$extension;
            $user->setImage($nombreImagen);

            try {
                $em->persist($user);
                $em->flush();

            }

            catch(\Exception $e) {
                return new Response('Esto ha petao');
            }
            $imagen->move("imgs/user",$nombreImagen);

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registro/registro.html.twig', [ 'controller_name'=> 'LoginController',
            'form'=> $form->createView()]);
    }

    /**
     * @Route("/registroOrganizacion", name="app_registroOrganizacion")
     */
    public function registroOrganizacion(UserPasswordHasherInterface $passwordHasher, Request $request, EntityManagerInterface $em): Response {
        $user=new User();

        $form=$this->createForm(Register2Type::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $a=['ROLE_ORG'];
            $user->setRoles($a);
            $hashedPasword=$passwordHasher->hashPassword($user,
                $form->get('password')->getData());
            $user->setPassword($hashedPasword);

            try {
                $em->persist($user);
                $em->flush();

            }

            catch(\Exception $e) {
                return new Response('Esto ha petao');
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registro/registroOrganizacion.html.twig', [ 'controller_name'=> 'LoginController',
            'form'=> $form->createView()]);
    }

    /**
     * @Route("/",name="app_main")
     */
    public function index(EntityManagerInterface $em) {

        /*
            BÚSQUEDA DE EVENTOS PARA MIOSTRARLOS EN EL INDEX
        */

        $todosLosEventos = $em->getRepository(Event::class)->findAll();
        $seMostraran = array_rand($todosLosEventos,6);

        if($this->getUser()) {

            foreach($this->getUser()->getRoles() as $rol) {

                if($rol=='ROLE_ORG') {
                    $tipoDeUsuario='ROLE_ORG';
                    return $this->render('main/index.html.twig', [ 'controller_name'=> 'MainController',
                        'role'=> $tipoDeUsuario,
                        'eventos' => $todosLosEventos
                        ]);
                }

                else if($rol=='ROLE_USER') {
                    $tipoDeUsuario='ROLE_USER';
                    return $this->render('main/index.html.twig', [ 'controller_name'=> 'MainController',
                        'role'=> $tipoDeUsuario,
                        'eventos' => $todosLosEventos

                        ]);
                }
            }
        }else {
            $tipoDeUsuario='no';
            return $this->render('main/index.html.twig', [ 'controller_name'=> 'MainController',
                'role'=> $tipoDeUsuario,
                'eventos' => $todosLosEventos
                ]);
        }
    }

    /**
        * @Route("/logout",name="app_logout")
        */
    public function loguot() {
        $tipoDeUsuario='no';
        return $this->render('main/index.html.twig', [ 'role'=> $tipoDeUsuario,
            ]);
    }

    /**
        * @Route("/perfil",name="app_perfil")
        */
    public function perfil(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $passwordHasher) {

        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var \App\Entity\User $user */
        $user=$this->getUser();
        $img=$user->getImage();
        echo $img;
        $form=$this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $emailForm=$form->get('email')->getData();

            if($emailForm==$user->getEmail()) {
                $existe=null;
            }

            else {
                $existe=$em->getRepository(User::class)->findOneBy(array('email'=> $emailForm));
            }


            if(is_null($existe)) {
                $user->setEmail($form->get('email')->getData());
                $user->setName($form->get('name')->getData());

                try {
                    $em->persist($user);
                    $em->flush();
                    return $this->render('perfil/perfil.html.twig', [ 'form'=> $form->createView(),
                    'imageBase64'=> $img,
                    'mensaje'=> 'Datos modificados correctamente',
                    'color' => 'verde'
                ]);
                }

                catch(\Exception $e) {
                    if($e->getCode()==1062) {
                        /** @var \App\Entity\User $user */
                        $user=$this->getUser();
                        $img=$user->getImage();
                        $form=$this->createForm(UserType::class, $user);

                        return $this->render('perfil/perfil.html.twig', [ 'form'=> $form->createView(),
                            'imageBase64'=> $img,
                            'mensaje'=> 'Ya existe un usuario con ese correo electrónico',
                            'color' => null
                            ]);
                    }

                    return $this->render('perfil/perfil.html.twig', [ 'form'=> $form->createView(),
                        'imageBase64'=> $img,
                        'mensaje'=> $e->getMessage()."----codigo: ".$e->getCode(),
                        'color' => null
                    ]);
                }
            }

            else {
                $mensaje='Ya existe un usuario con ese correo electrónico';
                return $this->render('perfil/perfil.html.twig', [ 'form'=> $form->createView(),
                    'imageBase64'=> $img,
                    'mensaje'=> $mensaje,
                    'color' => null
                ]);
            }
        }

        return $this->render('perfil/perfil.html.twig', [ 'form'=> $form->createView(),
            'imageBase64'=> $img,
            'mensaje'=> null,
            'color' => null
        ]);
    }
    /**
     * @Route("/perfilOrg", name="app_perfilORG")
     */
    public function perfilOrg(EntityManagerInterface $em, Request $request) {

        $this->denyAccessUnlessGranted('ROLE_ORG');
        /** @var \App\Entity\User $user */
        $user=$this->getUser();
        $img=$user->getImage();

        $form=$this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        $evento = new Event();
        $formularioEvento = $this->createForm(EventType::class, $evento);
        $formularioEvento->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $emailForm=$form->get('email')->getData();

            if($emailForm==$user->getEmail()) {
                $existe=null;
            }

            else {
                $existe=$em->getRepository(User::class)->findOneBy(array('email'=> $emailForm));
            }

            if(is_null($existe)) {
                $user->setEmail($form->get('email')->getData());
                $user->setName($form->get('name')->getData());

                try {
                    $em->persist($user);
                    $em->flush();
                    return $this->render('perfil/perfilORG.html.twig', [ 'form'=> $form->createView(),
                    'form2' => $formularioEvento->createView(),
                    'imageBase64'=> $img,
                    'mensaje'=> 'Datos modificados correctamente',
                    'color' => 'verde'
                ]);
                }

                catch(\Exception $e) {
                    if($e->getCode()==1062) {
                        /** @var \App\Entity\User $user */
                        $user=$this->getUser();
                        $img=$user->getImage();
                        $form=$this->createForm(UserType::class, $user);

                        return $this->render('perfil/perfilORG.html.twig', [ 'form'=> $form->createView(),
                            'form2' => $formularioEvento->createView(),
                            'imageBase64'=> $img,
                            'mensaje'=> 'Ya existe un usuario con ese correo electrónico',
                            'color' => null
                            ]);
                    }

                    return $this->render('perfil/perfilORG.html.twig', [ 'form'=> $form->createView(),
                        'form2' => $formularioEvento->createView(),
                        'imageBase64'=> $img,
                        'mensaje'=> $e->getMessage()."----codigo: ".$e->getCode(),
                        'color' => null
                    ]);
                }
            }

            else {
                $mensaje='Ya existe un usuario con ese correo electrónico';
                return $this->render('perfil/perfilORG.html.twig', [ 'form'=> $form->createView(),
                    'form2' => $formularioEvento->createView(),
                    'imageBase64'=> $img,
                    'mensaje'=> $mensaje,
                    'color' => null
                ]);
            }
        }

        
        

        return $this->render('perfil/perfilORG.html.twig', [ 'form'=> $form->createView(),
            'form2' => $formularioEvento->createView(),
            'imageBase64'=> $img,
            'mensaje'=> null,
            'color' => null
        ]);
    }

    /**
     * @Route("/crearEvento", name="app_crearEvento")
     */
    public function crearEvento(EntityManagerInterface $em, Request $request) {

        /** @var \App\Entity\User $user */
        $user=$this->getUser();

        $evento = new Event();
        $form = $this->createForm(EventType::class, $evento);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $evento->setUser($user);
            
            $imagen = $form->get('image')->getData();
            
            $extension = $imagen->guessExtension();
            $nombreImagen = "event".time(). "." .$extension;
            $imagen->move("imgs/event",$nombreImagen);
            $evento->setImage($nombreImagen);

            $evento->setTitle($form->get('title')->getData());
            $evento->setDescription($form->get('description')->getData());
            $evento->setDate($form->get('date')->getData());

            try {
                $em->persist($evento);
                $em->flush();
                
           } catch(\Exception $e) {
                echo $e->getMessage();
            }

        
        }
    return $this->render('crearEvento.html.twig', ['form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/misEventos", name="app_misEventos")
     */
    /*EVENTOS PARA EL BOTÓN "MIS EVENTOS" DEL ROL ORG*/
    public function verEventos(EntityManagerInterface $em, Request $request) {

        $this->denyAccessUnlessGranted('ROLE_ORG');

        /** @var \App\Entity\User $user */
        $user=$this->getUser();

        $arrayEventos = $em->getRepository(Event::class)->findBy(array('user'=>$user));
        $arrayComentarios = $em->getRepository(Assessment::class)->findBy(array('user_id'=>$user));

        return $this->render('misEventos.html.twig', ['eventos'=>$arrayEventos,
        'comentarios'=>$arrayComentarios
        ]);
    
    }

    /**
     * @Route("/eventos", name="app_eventos")
     */
    public function eventos(EntityManagerInterface $em) {

        $arrayEventos = $em->getRepository(Event::class)->findAll();

        $substr = '';
        
        foreach($arrayEventos as $evento) {
            $substr =  substr($evento->getDescription(),0,130);
            $evento->setDescription($substr.'...');
        }

        $arrayComentarios = $em->getRepository(Assessment::class)->findAll();
        return $this->render('Eventos/verEventos.html.twig', ['eventos' => $arrayEventos, 'comentarios' => $arrayComentarios]);
    }
    /**
     * @Route("visualizarEvento/{id}", name="app_visualizarEvento")
     */
    public function visualizarEvento(EntityManagerInterface $em,Request $request,$id) {
        
        /** @var \App\Entity\User $user */
        $user=$this->getUser();

        $comentarios = $em->getRepository(Assessment::class)->findBy(array('event'=>$id));
        $evento = $em->getRepository(Event::class)->findBy(array('id' => $id));
        
        $comentarioNuevo = new Assessment();
        $form = $this->createForm(AssessmentType::class, $comentarioNuevo);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $comentarioNuevo->setUser($user);
            $comentarioNuevo->setDescription($form->get('description')->getData());
            
            // FOREACH RECORRIENDO EL REPOSITORIO $EVENTO PARA DEFINIR EL EVENTO AL QUE PERTENECE EL COMENTARIO
            // EL REPOSITORIO SOLO CONTIENE UN ELEMENTO, YA QUE HA SIDO BUSCADO POR EL CAMPO ID, EL CUAL ES LA CLAVE PRIMARIA
            foreach($evento as $comentarioEvento) {
                $comentarioNuevo->setEvent($comentarioEvento);
            }

            try {

                $em->persist($comentarioNuevo);
                $em->flush();
                
                return $this->render('Eventos/visualizarEvento.html.twig', ['comentarios' => $comentarios, 'eventos' => $evento, 'form' => $form->createView(), 'correcto' => 'si']);

            }catch(\Exception $e) {
                echo $e->getMessage();
            }
        }

        return $this->render('Eventos/visualizarEvento.html.twig', ['comentarios' => $comentarios, 'eventos' => $evento, 'form' => $form->createView(),'correcto' => 'no']);

    }
}