<?php
session_start();

$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$dbname = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) { die("Помилка БД"); }

if(isset($_SESSION['LibrarianID'])) {
    $msg = "";
    
    if(isset($_POST['add-book'])) {
        $target_file = "";
        
        // Логіка завантаження фото
        if(!empty($_FILES["cover"]["name"])) {
            $target_directory = "../images/books_img/";
            $file_name = time() . "_" . basename($_FILES["cover"]["name"]); // Унікальне ім'я
            $target_file = $target_directory . $file_name;
            
            $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if(in_array($fileType, ['jpg', 'jpeg', 'png'])) {
                move_uploaded_file($_FILES["cover"]["tmp_name"], $target_file);
            }
        }

        $query = $conn->prepare("INSERT INTO books (Title, AuthorID, Publisher, Year, Category, Price, `Condition`, Abstract, Status, BookCover) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'в наявності', ?)");
        
        $params = [
            $_POST['title'],
            $_POST['author'],
            $_POST['publisher'],
            $_POST['year'],
            $_POST['category'],
            $_POST['price'],
            $_POST['condition'],
            $_POST['abstract'],
            $target_file
        ];

        if($query->execute($params)) {
            $msg = "success";
        }
    }

    if (isset($_GET['logOut'])) {
        session_destroy();
        header("Location: ../index.php");
        exit;
    }
?>
<!DOCTYPE html>
<html lang="uk_UA">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <title>Додати книгу | LibraVerse</title>
</head>
<body>
    <nav class="navbar navbar-default">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#menu">
                    <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                </button>
                <div class="navbar-logo">
                    <img src="../images/logo.svg" alt="логотип">
                    <a href="./home.php" id="main">LibraVerse</a>
                </div>
            </div>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="nav navbar-nav navbar-right text-center">
                  <li><a href="./home.php">Головна</a></li>
                  <li><a href="./customers_list.php">Клієнти</a></li>
                  <li><a href="./books_list.php">Книги</a></li>
                  <li><a href="./author_list.php">Автори</a></li> 
                  <li><a href="./librarians_list.php">Працівники</a></li>
                  <li><a href="./provision_list.php">Видача книг</a></li>
                  <li><a href="?logOut=1" id="logOut">Вийти</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container main-content add" style="padding-top: 20px;">
        <?php if($msg == "success"): ?>
            <div class="validation-msg done col-lg-12">
                <img src="../images/done.svg" alt="done">
                <h2>Книгу успішно додано!</h2>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-6">
                <form method="POST" enctype="multipart/form-data" class="book-form">
                    <h1 class="text-center">Додати нову книгу</h1>
                    <label>Назва</label>
                    <input type="text" name="title" class="form-input" required>
                    
                    <label>ID Автора</label>
                    <input type="number" name="author" class="form-input" required>
                    
                    <label>Видавництво</label>
                    <input type="text" name="publisher" class="form-input">
                    
                    <label>Рік</label>
                    <input type="number" name="year" class="form-input">

                    <label>Категорія</label>
                    <select name="category" class="form-input">
                        <option value="художня">художня</option>
                        <option value="технічна">технічна</option>
                        <option value="економічна">економічна</option>
                        <option value="комп'ютерна">комп'ютерна</option>
                        <option value="загальна" selected>загальна</option>
                    </select>

                    <label>Ціна</label>
                    <input type="number" name="price" class="form-input">

                    <label>Стан</label>
                    <select name="condition" class="form-input">
                        <option value="нова">нова</option>
                        <option value="незначно пошкоджена">незначно пошкоджена</option>
                        <option value="значно пошкоджена">значно пошкоджена</option>
                    </select>

                    <label>Анотація</label>
                    <textarea name="abstract" class="form-input" rows="4"></textarea>

                    <label>Обкладинка</label>
                    <input type="file" name="cover" accept="image/*">

                    <br>
                    <input type="submit" name="add-book" value="Зберегти книгу" class="btn btn-primary btn-block">
                </form>
            </div>
            <div class="col-lg-6 hidden-xs image">
                <img src="../images/add_book.png" class="img-responsive">
            </div>
        </div>
    </div>
        <footer class="footer col-lg-12">
        <div class="col-lg-9 footer-left">
            <p>Слідкуйте за нами:</p>
            <a href="#"><img src="../images/icon_facebook.svg" alt="фейсбук"></a>
            <a href="#"><img src="../images/icon-instagram.svg" alt="інстаграм"></a>
            <a href="#"><img src="../images/icon-twitterx.svg" alt="ікс"></a>
        </div>
        <div class="col-lg-3">
            <p>Зв’яжіться з нами: +380-88-675-89-12</p>
        </div>
        <div class="col-lg-12 text-center">
            <p>© 2026 LibraVerse. Всі права захищені.</p>
        </div>
    </footer>
</body>
</html>
<?php 
} else { header("Location: ../index.php"); exit; } 
?>