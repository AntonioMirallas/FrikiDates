<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use App\Entity\Usuario;
use App\Entity\Pregunta;

if(!isset($_SESSION)) 
    session_start();

class Seguridad extends AbstractController
{

    /**
     * @Route("/", name="portada")
     */
    public function portada(UserPasswordEncoderInterface $passwordEncoder): Response
    {
        // LOGIN
        $entrar = false;

        if(isset($_POST["login"])){

            $repositorioUsuarios = $this->getDoctrine()->getRepository(Usuario::class);
            if($usuario = $repositorioUsuarios->findOneBy(['email' => $_POST["emailLogin"]])) // Si existe el email

                if (password_verify($_POST["contraLogin"], $usuario->getPassword())){ // Si coincide la constraseña introducida con la contraseña cifrada por bcrypt

                    $_SESSION["usuario"] = $usuario;    
                    $entrar = true;
                }

        }

        if($entrar)
            return $this->redirectToRoute('principal');
        else
            return $this->render('portada.html.twig'); 

        // REGISTRO
        if(isset($_POST["registro"])){

            $repositorioUsuarios = $this->getDoctrine()->getRepository(Usuario::class);
            $usuarios = $repositorioUsuarios->findAll();
            if($repositorioUsuarios->findOneBy(['email' => $_POST["email"]])){

                $errorDuplicidad = "Email ya registrado";

            }

            else{

                $usuario = new Usuario($_POST["email"], $_POST["nombre"], $_POST["ciudad"], $_POST["contra1"], $_POST["edad"]);

                $usuario->setPassword( // Ciframos contraseña
                $passwordEncoder->encodePassword(
                    $usuario,
                    $_POST['contra1'])
                );

                $entityManager = $this->getDoctrine()->getManager(); // Introducimos el usuario en la BBDD
                $entityManager->persist($usuario);
                $entityManager->flush();

            }

        }

    }

    /**
     * @Route("/principal", name="principal")
     */
    public function principal(): Response
    {

        // RELLENAR FORMULARIO
        if(isset($_SESSION["usuario"])){

            $repositorioPreguntas = $this->getDoctrine()->getRepository(Pregunta::class);
            $videojuegos = $repositorioPreguntas->findBy(['tipo' => "Videojuegos"]);
            $cine = $repositorioPreguntas->findBy(['tipo' => "Cine"]);
            $libros = $repositorioPreguntas->findBy(['tipo' => "Libros"]);
            $anime = $repositorioPreguntas->findBy(['tipo' => "Anime"]);
            $randomObjetos = [];
            $randomRespuestas = [];

            // 5 PREGUNTAS DE CADA TIPO ALEATORIAS
            for ($i=0; $i < 5; $i++) { 
                
                $random = array_rand($videojuegos);
                $elemento = $videojuegos[$random];
                $randomObjetos[$i] = $elemento;

                unset($videojuegos[$random]);
            }

            for ($i=5; $i < 10; $i++) { 
                
                $random = array_rand($cine);
                $elemento = $cine[$random];
                $randomObjetos[$i] = $elemento;

                unset($cine[$random]);
            }

            for ($i=10; $i < 15; $i++) { 
                
                $random = array_rand($libros);
                $elemento = $libros[$random];
                $randomObjetos[$i] = $elemento;

                unset($libros[$random]);
            }

            for ($i=15; $i < 20; $i++) { 
                
                $random = array_rand($anime);
                $elemento = $anime[$random];
                $randomObjetos[$i] = $elemento;

                unset($anime[$random]);
            }

            $respuestas = [];

            shuffle($randomObjetos); // DESORDENAMOS LOS OBJETOS PARA MOSTRARSE MEJOR
            for ($i=0; $i < 20; $i++) { 
                $_SESSION["respuestas"][$i] = clone($randomObjetos[$i]);
            }


            for ($j=0; $j < 20; $j++) { // DESORDENAMOS LAS RESPUESTAS DE CADA PREGUNTA
                 
                    $respuestas = $randomObjetos[$j]->devolverRespuestas();
                    $randomObjetos[$j]->setFalsa1($respuestas[0]);
                    $randomObjetos[$j]->setFalsa2($respuestas[1]);
                    $randomObjetos[$j]->setFalsa3($respuestas[2]);
                    $randomObjetos[$j]->setCorrecta($respuestas[3]);

            }

            return $this->render('paginaPrincipal.html.twig', ["usuario"=>$_SESSION["usuario"], "objetos"=>$randomObjetos]);
        }

        else{

            return $this->render('portada.html.twig');
        }
    }

    /**
     * @Route("/grafico", name="grafico")
    */
    public function grafico(): Response
    {

        // ESTABLECER ESTADÍSTICAS SEGÚN LAS RESPUESTAS DEL FORMULARIO
        if(isset($_POST["formularioTerminado"])){

            $puntuaciones = ["Cine"=>0, "Videojuegos"=>0, "Anime"=>0, "Libros"=>0];
            $prueba = 0;

            for ($i=0; $i < 20; $i++) { 
                
                if($_SESSION["respuestas"][$i]->getCorrecta() == $_POST[$_SESSION["respuestas"][$i]->getId()])
                    $puntuaciones[$_SESSION["respuestas"][$i]->getTipo()]++;
            }

            var_dump($puntuaciones);
            echo $_SESSION["respuestas"][0]->getCorrecta();

            return $this->render('portada.html.twig');
        }

        return $this->render('portada.html.twig');
    }

    /**
     * @Route("/perfil", name="perfil")
     */
    public function perfil(): Response
    {
        
        return $this->render('perfil.html.twig');
    }
    // /**
    //  * @Route("/crear", name="crear")
    //  */
    // public function crear(Request $request): Response // Response devuelve una vista
    // {
        
    //     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    //     $user = $this->getUser();
    //     $entityManager = $this->getDoctrine()->getManager();
    //     $creado = 0;
    //     if(isset($_POST["nombreA"]) && isset($_POST["apellidosA"])){
    //         $actor = new Actor();
    //         $actor->setNombre($_POST["nombreA"]);
    //         $actor->setApellidos($_POST["apellidosA"]);
    //         $entityManager->persist($actor);
    //         $entityManager->flush();
    //         $creado = "El actor ha sido creado";
    //     }
    //     else if(isset($_POST["nombreD"]) && isset($_POST["apellidosD"])){
    //         $director = new Director();
    //         $director->setNombre($_POST["nombreD"]);
    //         $director->setApellidos($_POST["apellidosD"]);
    //         $entityManager->persist($director);
    //         $entityManager->flush();
    //         $creado = "El director ha sido creado";
    //     }
    //     else if(isset($_POST["localidad"]) && isset($_POST["nombreT"])){
    //         $teatro = new Teatro();
    //         $teatro->setLocalidad($_POST["localidad"]);
    //         $teatro->setNombre($_POST["nombreT"]);
    //         $entityManager->persist($teatro);
    //         $entityManager->flush();
    //         $creado = "El teatro ha sido creado";
    //     }
    //     return $this->render('crear.html.twig', ["user"=>$user,"creado"=>$creado]);
    // }
    // /**
    //  * @Route("/crearMusical", name="crearMusical")
    //  */
    // public function crearMusical(Request $request): Response // Response devuelve una vista
    // {
        
    //     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    //     $user = $this->getUser();
    //     $repositorioActores = $this->getDoctrine()->getRepository(Actor::class);
    //     $actores = $repositorioActores->findAll();
    //     $repositorioDirectores = $this->getDoctrine()->getRepository(Director::class);
    //     $directores = $repositorioDirectores->findAll();
    //     $escrito = false;
    //     if(isset($_POST["actores"]) && isset($_POST["nombreM"]) && isset($_POST["direct"]) && isset($_FILES['foto']['tmp_name'])){
    //         move_uploaded_file($_FILES['foto']['tmp_name'], '../public/images/'.$_FILES['foto']['name']);
    //         $_POST["foto"] = 'images/'.$_FILES['foto']['name'];
    //         $entityManager = $this->getDoctrine()->getManager();
    //         $director = $repositorioDirectores->findOneBy(['id' => $_POST["direct"]]);
    //         $musical = new Musical();
    //         $musical->setNombre($_POST["nombreM"]);
    //         $musical->setFoto($_POST["foto"]);
    //         $musical->setDirectorDirige($director);
    //         $actoresRecibidos = $_POST["actores"];
    //         $tamActores = count($actoresRecibidos);
    //         for ($i=0; $i < $tamActores; $i++) { 
                
    //             $actor = $repositorioActores->findOneBy(['id' => $_POST["actores"][$i]]);
    //            $musical->addActorActuando($actor); 
    //         }
            
    //         $entityManager->persist($musical);
    //         $entityManager->flush();
    //         $escrito = true;
    //         return $this->render('crearMusical.html.twig', ["user"=>$user, "escrito"=>$escrito, "actores"=>$actores, "directores"=>$directores]);
    //     }
    //     else
    //         return $this->render('crearMusical.html.twig', ["user"=>$user, "escrito"=>$escrito, "actores"=>$actores, "directores"=>$directores]);
    // }
    // /**
    //  * @Route("/crearSesion", name="crearSesion")
    //  */
    // public function crearSesion(Request $request): Response // Response devuelve una vista
    // {
        
    //     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    //     $user = $this->getUser();
    //     $entityManager = $this->getDoctrine()->getManager();
    //     $repositorioSesiones = $this->getDoctrine()->getRepository(Sesion::class);
    //     $repositorioTeatros = $this->getDoctrine()->getRepository(Teatro::class);
    //     $teatros = $repositorioTeatros->findAll();
    //     $repositorioMusicales = $this->getDoctrine()->getRepository(Musical::class);
    //     $musicales = $repositorioMusicales->findAll();
    //     // $repositorioDirectores = $this->getDoctrine()->getRepository(Director::class);
    //     // $directores = $repositorioDirectores->findAll();
    //     $escrito = false;
    //     if(isset($_POST["fechaS"]) && isset($_POST["teatro"]) && isset($_POST["musical"])){
    //         $teatro = $repositorioTeatros->findOneBy(['id' => $_POST["teatro"]]);
    //         $musical = $repositorioMusicales->findOneBy(['id' => $_POST["musical"]]);
    //         $sesion = new Sesion();
    //         $sesion->setFecha($_POST["fechaS"]);
    //         $sesion->setTeatroSesion($teatro);
    //         $sesion->setMusicalSesion($musical);
    //         $entityManager->persist($sesion);
    //         $entityManager->flush();
    //         $escrito = true;
    //         return $this->render('crearSesion.html.twig', ["user"=>$user, "escrito"=>$escrito, "teatros"=>$teatros, "musicales"=>$musicales]);
    //     }
    //     else
    //         return $this->render('crearSesion.html.twig', ["user"=>$user, "escrito"=>$escrito, "teatros"=>$teatros, "musicales"=>$musicales]);
    // }
    // /**
    //  * @Route("/mostrarSesiones", name="mostrarSesiones")
    //  */
    // public function mostrarSesiones(Request $request): Response // Response devuelve una vista
    // {
        
    //     $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
    //     $user = $this->getUser();
    //     $entityManager = $this->getDoctrine()->getManager();
    //     $repositorioSesiones = $this->getDoctrine()->getRepository(Sesion::class);
    //     $sesiones = $repositorioSesiones->findAll();
    //     return $this->render('mostrarSesiones.html.twig', ["sesiones"=>$sesiones]);
    // }
}