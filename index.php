<?php
require_once 'API/vendor/autoload.php';

session_start();

$client = new Google_Client();
$client->setAuthConfig('secrets.json');
$client->addScope(Google_Service_YouTube::YOUTUBE_READONLY);

if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);

    if ($client->isAccessTokenExpired()) {
        unset($_SESSION['access_token']);
        $authUrl = $client->createAuthUrl();
    }
} else {
    $authUrl = $client->createAuthUrl();

}

if (isset($_GET['code'])) {
    $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    header('Location: ' . filter_var($_SERVER['PHP_SELF'], FILTER_SANITIZE_URL));
    exit();
}
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube API</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/9d20b7b75d.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="main.js"></script>
    <script src="tailwind.js"></script>
</head>
<body>

<div class="flex flex-row p-12 h-screen">
        <?php
        if (isset($authUrl)) {
            // Mostrar enlace de inicio de sesión
            echo '
<section class="container-left h-full w-full rounded-[18px] flex flex-col">
        <div class="text-primary flex justify-center items-center p-[40px] mt-[150px] ">
            <div class="relative">
                <div class="w-[150px] h-[150px] bg-primaryLight rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-bug text-primary text-[50px]"></i>
                </div>
            </div>
        </div>
        <div class=" flex justify-center items-center p-8">
            <span class="text-primary text-[40px] font-medium"> YouTube - API</span>
        </div>




            <div class="flex justify-center items-center p-8" id="loggedOutContent">
                <a class="bg-primaryDark text-center hover:bg-secondaryLight text-white hover:text-white font-bold py-2 px-8 rounded-[20px] text-[20px] w-[250px]" href="' . filter_var($authUrl, FILTER_SANITIZE_URL) . '">Iniciar Sesión</a>
            </div>
            </section>
            
            ';
        } else {
            // Mostrar enlace de cierre de sesión
            echo '
<section class="container-left h-full w-3/6 rounded-l-[18px] flex flex-col">
        <div class="text-primary flex justify-center items-center p-[40px] mt-[150px] ">
            <div class="relative">
                <div class="w-[150px] h-[150px] bg-primaryLight rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-bug text-primary text-[50px]"></i>
                </div>
            </div>
        </div>
        <div class=" flex justify-center items-center p-8">
            <span class="text-primary text-[40px] font-medium"> YouTube - API</span>
        </div>
                <div class="flex justify-center items-center p-8" id="loggedInContent">
                    <a class="bg-primaryDark text-center hover:bg-secondaryLight text-white hover:text-white font-bold py-2 px-8 rounded-[20px] text-[20px] w-[250px]" href="logout.php">Cerrar Sesión</a>
                </div>
                </section>
            ';
        }


        $youtubeService = new Google_Service_YouTube($client);
        $channelsResponse = $youtubeService->channels->listChannels('snippet,statistics', array('mine' => true));


        // Realizar la búsqueda de videos
        $channel = $channelsResponse->getItems()[0];


        $nombre = $channel['snippet']['title'];
        $usuario = $channel['snippet']['customUrl'];
        $createdAt = $channel['snippet']['publishedAt'];
        $views = $channel['statistics']['viewCount'];
        $subs = $channel['statistics']['subscriberCount'];
        $videos = $channel['statistics']['videoCount'];
        $image = $channel['snippet']['thumbnails']['high']['url'];
        $date = date_create($createdAt);
        $date = date_format($date, 'Y-m-d H:i:s');





        ?>



        <!--ANTES DE LOGEARSE-->

        <?php
        if (!$_SESSION['access_token'] === false) {

            ?>


            <section class="container-right h-full w-3/6 rounded-r-[18px] flex justify-center items-center flex-col">

                <div class="flex justify-center items-center p-8 text-secondaryDark text-[40px] font-medium">
                    ¡Bienvenido &nbsp;<span class="text-primary text-[40px] font-medium"><?php echo $nombre ?>!</span>
                </div>
                <div class="card flex-col text-secondaryDark">
                    <h3 class="card-title text-center">Cuenta</h3>
                    <div class="flex items-center justify-center">
                        <div class="rounded-full h-32 w-32 overflow-hidden">
                            <img src="<?php echo $image ?>" alt="Foto de perfil" class="h-full w-full object-cover" />
                        </div>
                    </div>

                    <br>
                    <div class="flex items-center justify-center">
                        <ul>
                            <li class="mb-2 p-2 border border-white-300 rounded-md"><span class="font-bold">Nombre:</span> <?php echo $nombre ?></li>
                            <li  class="mb-2 p-2 border border-white-300  rounded-md"><span class="font-bold">Usuario: </span><?php echo $usuario ?></li>
                            <li  class="mb-2 p-2 border border-white-300 rounded-md"><span class="font-bold">Cuenta creada el: </span><?php echo $date ?></li>
                            <li class="mb-2 p-2 border border-white-300  rounded-md"><span class="font-bold">Visualizaciones: </span><?php echo $views ?></li>
                            <li class="mb-2 p-2 border border-white-300  rounded-md"><span class="font-bold">Suscriptores: </span><?php echo $subs ?></li>
                            <li class="mb-2 p-2 border border-white-300  rounded-md"><span class="font-bold">Videos subidos: </span><?php echo $videos ?></li>
                        </ul>
                    </div>


                    <h3 class="mt-4 card-title text-center">Últimos videos subidos</h3>
                    <div class="grid grid-cols-3 gap-4">
                    <?php

                    $videosResponse = $youtubeService->search->listSearch('snippet', array(
                        'channelId' => $channel['id'],
                        'maxResults' => 10,
                        'order' => 'date',
                        'type' => 'video'
                    ));

                     foreach ($videosResponse->getItems() as $video) {
                        $videoTitle = $video['snippet']['title'];
                        $videoId = $video['id']['videoId'];
                        $videoThumbnail = $video['snippet']['thumbnails']['medium']['url'];
                         $description = $video['snippet']['description'];
                    ?>

                    <div class="flex flex-col items-center justify-center">
                            <iframe width="200" height="200" src="https://www.youtube.com/embed/<?php echo $videoId; ?>"
                                    title="<?php echo $videoTitle; ?>" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen>
                            </iframe>
                            <p class="mt-2 text-center"><?php echo $videoTitle; ?></p>
                            <p class="text-center"><?php echo $description; ?></p>

                    </div>

                    <?php } ?>
                    </div>
                </div>
            </section>


            <?php
        }
        ?>





</div>

</body>
</html>
