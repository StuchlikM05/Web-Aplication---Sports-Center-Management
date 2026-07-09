<?php
// Rezervace.php

// Načteme společné soubory, včetně konfigurace
require 'config.php';

// Připojení k databázi
$conn = getDBConnection();

// Smazání starých rezervací (starších než dnešní datum)
$conn->query("DELETE FROM bookings WHERE datum_rezervace < CURDATE()");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Bez sanitizace vstupů, ale stále doporučujeme používat clean_input v praxi
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $facility = intval($_POST['facility']);
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Kontrola, zda je čas již obsazen
    $stmt = $conn->prepare("
    SELECT COUNT(*) FROM bookings 
    WHERE prostor_ID = ? AND datum_rezervace = ? 
    AND ((zacatek < ? AND konec > ?) OR (zacatek < ? AND konec > ?) OR (zacatek >= ? AND konec <= ?))
    ");
    $stmt->bind_param("isssssss", $facility, $date, $end_time, $end_time, $start_time, $start_time, $start_time, $end_time);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        echo "<script>alert('Tento termín je již obsazen! Vyberte jiný čas.'); window.history.back();</script>";
        exit;
    }

    // Spuštění transakce
    $conn->begin_transaction();
    try {
        $customer_id = null;

        // 1) Hledáme zákazníka podle e-mailu (nejdůležitější kritérium)
        $stmt = $conn->prepare("SELECT ID_zakaznika FROM customers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $customer_id = $row['ID_zakaznika'];
        } else {
            // 2) Hledáme zákazníka podle jména a příjmení, pokud neexistuje e-mail
            $stmt = $conn->prepare("SELECT ID_zakaznika FROM customers WHERE name = ? AND surname = ?");
            $stmt->bind_param("ss", $name, $surname);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Pokud existuje shoda ve jméně a příjmení, použijeme existujícího zákazníka
                $row = $result->fetch_assoc();
                $customer_id = $row['ID_zakaznika'];
            } else {
                // 3) Pokud žádná shoda neexistuje, vytvoříme nového zákazníka
                $stmt = $conn->prepare("INSERT INTO customers (name, surname, email, telefon, vytvoreno_v) VALUES (?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssss", $name, $surname, $email, $phone);
                $stmt->execute();
                $customer_id = $stmt->insert_id;
            }
        }
        $stmt->close();

        // Vložení rezervace
        $stmt = $conn->prepare("INSERT INTO bookings (ID_zakaznika, prostor_ID, datum_rezervace, zacatek, konec, vytvore_v) 
                                VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("iisss", $customer_id, $facility, $date, $start_time, $end_time);
        $stmt->execute();
        $stmt->close();

        // Dokončení transakce
        $conn->commit();

        // Přesměrování na děkovnou stránku
        header('Location: dekujeme.php');
        exit;
    } catch (Exception $e) {
        // Vrácení změn v případě chyby
        $conn->rollback();
        echo "Chyba při zpracování rezervace: " . $e->getMessage();
    }
}

// Uzavření připojení k databázi
$conn->close();
?>



<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="icon" type="image/png" href="img/logo.svg">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <title>Rezervace</title>
</head>
<body id="page-5">
    <header>
        <div id="nav">
            <p><a href="index.html"><img src="img/logo.svg" alt=""></a></p>
            <div class="burger-menu" onclick="toggleMenu()">&#9776;</div>
            <ul id="nav-menu">
                <li><a href="index.html">Úvod</a></li>
                <li><a href="viceinf.html">O nás</a></li>
                <li><a href="blog.html">Blog</a></li>
                <li><a href="cenik.html">Ceník</a></li>
                <li><a class="active" href="rezervace.php">Rezervace</a></li>
            </ul>
        </div>
    </header>

    <main>
        <section id="reservation-form">
            <h1>Rezervace</h1>
            <div id="reservation-content" class="container">
                <div id="form">
                    <form method="POST" action="rezervace.php">
                        <label for="name">Jméno:</label>
                        <input type="text" id="name" name="name" required>

                        <label for="surname">Příjmení:</label>
                        <input type="text" id="surname" name="surname" required>

                        <label for="email">E-mail:</label>
                        <input type="email" id="email" name="email" required>

                        <label for="phone">Telefon:</label>
                        <input type="tel" id="phone" name="phone" required>

                        <label for="facility">Prostor:</label>
                        <select id="facility" name="facility" required>
                        <?php
                        $conn = new mysqli('localhost', 'c21D21632', 'e3/z_5iQhd/lysk-', 'c21sportrental');
                        $result = $conn->query("SELECT * FROM facilities");

                        while ($row = $result->fetch_assoc()) {
                            $selected = (isset($_GET['id']) && $_GET['id'] == $row['prostor_ID']) ? 'selected' : '';
                            echo "<option value='" . $row['prostor_ID'] . "' $selected>" . $row['nazev'] . " - " . $row['misto'] . "</option>";
                        }
                        $conn->close();
                        ?>
                        </select>

                        <label for="date">Datum rezervace:</label>
                        <input type="date" id="date" name="date" required>

                        <label for="start_time">Začátek:</label>
                        <input type="time" id="start_time" name="start_time" required>

                        <label for="end_time">Konec:</label>
                        <input type="time" id="end_time" name="end_time" required>

                        <input class="btn" type="submit" value="Odeslat">
                    </form>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <p>Jedná se o fiktivní projekt pro závěrečnou maturitní práci.</p>
    </footer>

    <script src="script.js"></script>
</body>
</html>
