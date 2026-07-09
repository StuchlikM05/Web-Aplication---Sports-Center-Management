<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poděkování za rezervaci</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="icon" type="image/png" href="img/logo.svg">
</head>
<body id="page-6">
    <header>
        <div id="nav">
            <p><a href="index.html"><img src="img/logo.svg" alt=""></a></p>
            <div class="burger-menu" onclick="toggleMenu()">&#9776;</div>
            <ul id="nav-menu">
                <li><a href="index.html">Úvod</a></li>
                <li><a href="viceinf.html">O nás</a></li>
                <li><a href="blog.html">Blog</a></li>
                <li><a href="cenik.html">Ceník</a></li>
                <li><a href="rezervace.php">Rezervace</a></li>
            </ul>
        </div>
    </header>

    <main>
        <section id="thank-you">
            <h1>Děkujeme za vaši rezervaci!</h1>
            <p>Vaše rezervace byla úspěšně odeslána. Brzy vás budeme kontaktovat pro potvrzení.</p>
            <input class="btn" type="button" value="Zpět na úvodní stránku" onclick="window.location.href='index.html'">
        </section>
    </main>

    <footer>
        <p>Jedná se o fiktivní projekt pro závěrečnou maturitní práci.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
