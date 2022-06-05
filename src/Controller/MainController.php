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
            'role' => 'no'
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
            $imagen = $form->get('image')->getData();
             
            $roleForm=['ROLE_USER'];
            $user->setRoles($roleForm);

            $hashedPasword=$passwordHasher->hashPassword($user,
            $form->get('password')->getData());
            $user->setPassword($hashedPasword);

            $imagen = $form->get('image')->getData();
            $extension = $imagen->guessExtension();
            if($extension != 'jpg' && $extension != 'png' && $extension != 'jpeg') {
                return $this->render('registro/registro.html.twig', [ 'controller_name'=> 'LoginController',
                'form'=> $form->createView(),'role'=>'no', 'mensaje' => 'Introduce una imagen con un formato correcto (jpeg,png,jpg)' ]);
            }

            $nombreImagen = "user".time(). "." .$extension;
            $imagen->move("imgs/user",$nombreImagen);
            $user->setImage($nombreImagen);

            try {
                $em->persist($user);
                $em->flush();

            }

            catch(\Exception $e) {
                return new Response('Esto ha petao');
            }
            

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registro/registro.html.twig', [ 'controller_name'=> 'LoginController',
            'form'=> $form->createView(),'role'=>'no', 'mensaje' => '' ]);
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
                return $this->render('registro/registroOrganizacion.html.twig', [ 'controller_name'=> 'LoginController',
            'form'=> $form->createView(), 'role'=>'no']);
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registro/registroOrganizacion.html.twig', [ 'controller_name'=> 'LoginController',
            'form'=> $form->createView(), 'role'=>'no']);
    }

    /**
     * @Route("/",name="app_main")
     */
    public function index() {

        if($this->getUser()) {

            foreach($this->getUser()->getRoles() as $rol) {

                if($rol=='ROLE_ORG') {
                    $tipoDeUsuario='ROLE_ORG';
                    return $this->render('inicio.html.twig', [ 'controller_name'=> 'MainController',
                        'role'=> $tipoDeUsuario
                        ]);
                }

                else if($rol=='ROLE_USER') {
                    $tipoDeUsuario='ROLE_USER';
                    return $this->render('inicio.html.twig', [ 'controller_name'=> 'MainController',
                        'role'=> $tipoDeUsuario

                        ]);
                }
            }
    
        }else {
            $tipoDeUsuario='no';
            return $this->render('inicio.html.twig', [ 'controller_name'=> 'MainController',
                'role'=> $tipoDeUsuario
                ]);
        }
    }

        /**
        * @Route("/logout",name="app_logout")
        */
    public function loguot() {
        $tipoDeUsuario='no';
        return $this->render('main/.html.twig', [ 'role'=> $tipoDeUsuario,
            ]);
    }

    /**
        * @Route("/perfil",name="app_perfil")
        */
    public function perfil(EntityManagerInterface $em, Request $request, UserPasswordHasherInterface $passwordHasher) {

        $this->denyAccessUnlessGranted('ROLE_USER');
        /** @var \App\Entity\User $user */
        $user=$this->getUser();
        return $this->render('perfil/perfil.html.twig', [ 
            'mensaje'=> null,
            'color' => null,
            'role' => 'ROLE_USER',
            'user' => $user
        ]);
        
       /* $img=$user->getImage();
        $form=$this->createForm(UserInfoType::class, $user);
        $form->handleRequest($request); */

    

       /* if($form->isSubmitted() && $form->isValid()) {

            $emailForm=$form->get('email')->getData();

            if($emailForm==$user->getEmail()) {
                $existe=null;
            }

            else {
                $existe=$em->getRepository(User::class)->findOneBy(array('email'=> $emailForm));
            }return $this->render('perfil/perfil.html.twig', [ 'form'=> $form->createView(),
            'imageBase64'=> $img,
            'mensaje'=> null,
            'color' => null,
            'role' => 'ROLE_USER'
        ]);


            if(is_null($existe)) {
                $user->setEmail($form->get('email')->getData());
                $user->setName($form->get('name')->getData());

                try {
                    $em->persist($user);
                    $em->flush();
                    return $this->render('perfil/perfil.html.twig', [ 'form'=> $form->createView(),
                    'imageBase64'=> $img,
                    'mensaje'=> 'Datos modificados correctamente',
                    'color' => 'verde',
                    'role' => 'ROLE_USER'
                ]);
                }

                catch(\Exception $e) {
                    if($e->getCode()==1062) {
                        /** @var \App\Entity\User $user */
            /*            $user=$this->getUser();
                        $img=$user->getImage();
                        $form=$this->createForm(UserType::class, $user);

                        return $this->render('perfil/perfil.html.twig', [ 'form'=> $form->createView(),
                            'imageBase64'=> $img,
                            'mensaje'=> 'Ya existe un usuario con ese correo electrónico',
                            'color' => null,
                            'role' => 'ROLE_USER'
                            ]);
                    }

                    return $this->render('perfil/perfil.html.twig', [ 'form'=> $form->createView(),
                        'imageBase64'=> $img,
                        'mensaje'=> $e->getMessage()."----codigo: ".$e->getCode(),
                        'color' => null,
                        'role' => 'ROLE_USER'
                    ]);
                }
            }

            else {
                $mensaje='Ya existe un usuario con ese correo electrónico';
                return $this->render('perfil/perfil.html.twig', [ 'form'=> $form->createView(),
                    'imageBase64'=> $img,
                    'mensaje'=> $mensaje,
                    'color' => null,
                    'role' => 'ROLE_USER'
                ]);
            }
        } 
    */
   
    }
    
    /**
     * @Route("/deleteUser/{user}", name="app_deleteUser")
     */
    public function delteUser(int $user,EntityManagerInterface $em,AuthenticationUtils $authenticationUtils) {   
        $usuarioObj = $em->getRepository(User::class)->findBy(array('id'=>$user));
        
        $susComentarios = $em->getRepository(Assessment::class)->findBy(array('user'=>$usuarioObj));

        //ELIMINA TODOS LOS COMENTARIOS DEL USUARIO
        foreach($susComentarios as $comentario) {
                try {

                    $em->remove($comentario);
                    $em->flush();

                }catch(\Exception $e) {
                    echo $e->getMessage();
                }
        }
        //ELIMINA LA CUENTA
        try {
            foreach($usuarioObj as $usuario) {
                $em->remove($usuario);
                $em->flush();

                return $this->redirectToRoute("app_login");
            }
            

        }catch(\Exception $e) {
            echo $e->getMessage();
        }

        $error=$authenticationUtils->getLastAuthenticationError();
        $lastUsername=$authenticationUtils->getLastUsername();


        return $this->render('login/login.html.twig', [ 'controller_name'=> 'MainController',
            'error'=> $error,
            'last_username'=> $lastUsername,
            'role' => 'no'
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
        

        return $this->render('perfil/perfilORG.html.twig', [ 'form'=> $form->createView(),
            'form2' => $formularioEvento->createView(),
            'user'=> $user,
            
            'role' => 'ROLE_ORG'
        ]);
/*
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
 /*                       $user=$this->getUser();
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
*/

    }

    /**
     * @Route("/crearEvento", name="app_crearEvento")
     */
    public function crearEvento(EntityManagerInterface $em, Request $request) {
        $this->denyAccessUnlessGranted('ROLE_ORG');
        
        /** @var \App\Entity\User $user */
        $user=$this->getUser();
        //echo phpinfo();
        $evento = new Event();
        $form = $this->createForm(EventType::class, $evento);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $evento->setUser($user);
            
            $imagen = $form->get('image')->getData();  
            $extension = $imagen->guessExtension();
            if($extension != 'jpg' && $extension != 'png' && $extension != 'jpeg') {
                return $this->render('crearEvento.html.twig', ['form'=>$form->createView(), 'role' => 'ROLE_ORG', 'mensaje' => '', 'mensajeImagen' => 'Introduce una imagen con un formato correcto (jpeg,png,jpg)' 
                ]);
            }
            $nombreImagen = "event".time(). "." .$extension;
            $imagen->move("imgs/event",$nombreImagen);
            $evento->setImage($nombreImagen);

            $evento->setTitle($form->get('title')->getData());
            $evento->setDescription($form->get('description')->getData());
            $evento->setDate($form->get('date')->getData());

            $fechaActual = new \DateTime();
            $fechaEvento = $form->get('date')->getData();
            if($fechaEvento < $fechaActual) {
                return $this->render('crearEvento.html.twig', ['form'=>$form->createView(), 'role' => 'ROLE_ORG', 'mensaje' => 'Introduce una fecha mayor a la actual', 'mensajeImagen'=> ''
                ]);
            }
            try {

                $em->persist($evento);
                $em->flush();
                
           } catch(\Exception $e) {
                echo $e->getMessage();
            }

        
        }
    return $this->render('crearEvento.html.twig', ['form'=>$form->createView(), 'role' => 'ROLE_ORG', 'mensaje' => '', 'mensajeImagen'=> ''
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
        $arrayComentarios = $em->getRepository(Assessment::class)->findAll();

        return $this->render('misEventos.html.twig', ['eventos'=>$arrayEventos,
        'comentarios'=>$arrayComentarios, 'role' => 'ROLE_ORG'
        ]);
    
    }

    /**
     * @Route("/eventos", name="app_eventos")
     */
    public function eventos(EntityManagerInterface $em) {
        
        $arrayEventos = $em->getRepository(Event::class)->findAll();

        $substr = '';

        /** @var \App\Entity\User $user */
        $user=$this->getUser();
        $arrayComentarios = $em->getRepository(Assessment::class)->findAll();

        foreach($arrayEventos as $evento) {
            $substr =  substr($evento->getDescription(),0,130);
            $evento->setDescription($substr.'...');
        }

         
        $tipoDeUsuario = '';
        
        if($user) {

            foreach($user->getRoles() as $rol) {
               

                if($rol=='ROLE_ORG') {
                    $tipoDeUsuario='ROLE_ORG';
                    return $this->render('Eventos/verEventos.html.twig', ['eventos' => $arrayEventos, 'comentarios' => $arrayComentarios, 'role' => $tipoDeUsuario]);

                    
                }

                else if($rol==='ROLE_USER') {
                    $tipoDeUsuario='ROLE_USER';
                    return $this->render('Eventos/verEventos.html.twig', ['eventos' => $arrayEventos, 'comentarios' => $arrayComentarios, 'role' => $tipoDeUsuario]);

                }
                echo "$tipoDeUsuario<br>";
            }

        }else {
            $tipoDeUsuario='no';
            
        }

        return $this->render('Eventos/verEventos.html.twig', ['eventos' => $arrayEventos, 'comentarios' => $arrayComentarios, 'role' => $tipoDeUsuario]);
    }
    /**
     * @Route("visualizarEvento/{id}", name="app_visualizarEvento")
     */
    public function visualizarEvento(EntityManagerInterface $em,Request $request,$id) {
        
        $this->denyAccessUnlessGranted("ROLE_USER");
        
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
                $comentarios = $em->getRepository(Assessment::class)->findBy(array('event'=>$id));

                if($user) {

                    foreach($user->getRoles() as $rol) {
                       
        
                        if($rol=='ROLE_ORG') {
                            $tipoDeUsuario='ROLE_ORG';
                            return $this->render('Eventos/visualizarEvento.html.twig', ['comentarios' => $comentarios, 'eventos' => $evento, 'form' => $form->createView(), 'role' => $tipoDeUsuario]);
        
                            
                        }
        
                        else if($rol==='ROLE_USER') {
                            $tipoDeUsuario='ROLE_USER';
                            return $this->render('Eventos/visualizarEvento.html.twig', ['comentarios' => $comentarios, 'eventos' => $evento, 'form' => $form->createView(), 'role' => $tipoDeUsuario]);
        
                        }
                       
                    }
        
                }else {
                    $tipoDeUsuario='no';
                    return $this->render('Eventos/visualizarEvento.html.twig', ['comentarios' => $comentarios, 'eventos' => $evento, 'form' => $form->createView(), 'role' => $tipoDeUsuario]);
                }

                

            }catch(\Exception $e) {
                echo $e->getMessage();
            }
        }

        if($user) {

            foreach($user->getRoles() as $rol) {
               

                if($rol=='ROLE_ORG') {
                    $tipoDeUsuario='ROLE_ORG';
                    return $this->render('Eventos/visualizarEvento.html.twig', ['comentarios' => $comentarios, 'eventos' => $evento, 'form' => $form->createView(), 'role' => $tipoDeUsuario]);

                    
                }

                else if($rol==='ROLE_USER') {
                    $tipoDeUsuario='ROLE_USER';
                    return $this->render('Eventos/visualizarEvento.html.twig', ['comentarios' => $comentarios, 'eventos' => $evento, 'form' => $form->createView(), 'role' => $tipoDeUsuario]);

                }
               
            }

        }else {
            $tipoDeUsuario='no';
            return $this->render('Eventos/visualizarEvento.html.twig', ['comentarios' => $comentarios, 'eventos' => $evento, 'form' => $form->createView(), 'role' => $tipoDeUsuario]);
        }

        

    }

    /**
     * @Route("/organizaciones/{id_org}", name="app_organizaciones")
     */
    public function organizaciones(EntityManagerInterface $em, String $id_org) {

        $tipoDeUsuario = '';
        if($this->getUser()) {

            foreach($this->getUser()->getRoles() as $rol) {

                if($rol=='ROLE_ORG') {
                    $tipoDeUsuario='ROLE_ORG';
                    
                    if($id_org == 0) {
            
                        $arrayOrganizaciones = $em->getRepository(User::class)->findByRole('ORG');
                        $arrayEventos = $em->getRepository(Event::class)->findAll();
                        
                        return $this->render('organizaciones.html.twig', ['role'=>$tipoDeUsuario, 'arrayOrgs'=>$arrayOrganizaciones, 'arrayEventos'=>$arrayEventos, 'titulo'=>'Listado de las organizaciones']);
            
                    } else {
                        
                        $arrayEventos = $em->getRepository(Event::class)->findBy(array('user'=>$id_org));
                        $arrayComentarios = $em->getRepository(Assessment::class)->findAll();
                        return $this->render('organizaciones.html.twig', ['role'=>$tipoDeUsuario, 'eventos', 'arrayEventos'=>$arrayEventos, 'arrayOrgs'=>'', 'comentarios' => $arrayComentarios, 'titulo' => 'Listado de eventos']);
                    }
                    
                }

                else if($rol=='ROLE_USER') {
                    $tipoDeUsuario='ROLE_USER';
                    if($id_org == 0) {
            
                        $arrayOrganizaciones = $em->getRepository(User::class)->findByRole('ORG');
                        $arrayEventos = $em->getRepository(Event::class)->findAll();
                        
                        return $this->render('organizaciones.html.twig', ['role'=>$tipoDeUsuario, 'arrayOrgs'=>$arrayOrganizaciones, 'arrayEventos'=>$arrayEventos, 'titulo'=>'Listado de las organizaciones']);
            
                    } else {
                        
                        $arrayEventos = $em->getRepository(Event::class)->findBy(array('user'=>$id_org));
                        $arrayComentarios = $em->getRepository(Assessment::class)->findAll();
                        return $this->render('organizaciones.html.twig', ['role'=>$tipoDeUsuario, 'eventos', 'arrayEventos'=>$arrayEventos, 'arrayOrgs'=>'', 'comentarios' => $arrayComentarios, 'titulo' => 'Listado de eventos']);
                    }
                    
                }
            }
        }else {
            $tipoDeUsuario='no';
            if($id_org == 0) {
            
                $arrayOrganizaciones = $em->getRepository(User::class)->findByRole('ORG');
                $arrayEventos = $em->getRepository(Event::class)->findAll();
                
                return $this->render('organizaciones.html.twig', ['role'=>$tipoDeUsuario, 'arrayOrgs'=>$arrayOrganizaciones, 'arrayEventos'=>$arrayEventos, 'titulo'=>'Listado de las organizaciones']);
    
            } else {
                
                $arrayEventos = $em->getRepository(Event::class)->findBy(array('user'=>$id_org));
                $arrayComentarios = $em->getRepository(Assessment::class)->findAll();
                return $this->render('organizaciones.html.twig', ['role'=>$tipoDeUsuario, 'eventos', 'arrayEventos'=>$arrayEventos, 'arrayOrgs'=>'', 'comentarios' => $arrayComentarios, 'titulo' => 'Listado de eventos']);
            }
            
        }
        
        
    }
}