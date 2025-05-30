<!DOCTYPE html>
<html lang="en">

<head>
  <?php require "page/head.php" ?>
  <style>
    @import url(css/navLinks.css);
    @import url(css/titles.css);
    @import url(css/form.css);
    @import url(css/footer.css);
  </style>
</head>

<body>
  <?php session_start(); ?>
  <div class="title--social10">
    <h1>Social10</h1>
  </div>
  <div class="nav-links">
    <nav>
      <a href="postsFeed.php">Posts</a> |
      <a href="index.php" class="active-link">Sign up</a> |
      <?php if (isset($_SESSION["userID"])) { ?>
        <a href="logout.php">Log out</a>
      <?php } else { ?>
        <a href="login.php">Log in</a>
      <?php } ?>
    </nav>
  </div>
  <div class="title--auth">
    <h2>Sign up</h2>
  </div>
  <?php
  require "actions.php";

  if (isset($_POST["username"]) && isset($_POST["password"])) {
    $username = $_POST["username"];
    $password = hash("ripemd128", $_POST["password"]);

    $mysql = connectToMySQL();
    $result = runSelectQuery($mysql, "select * from users_social10 where username = ?", "s", $username);

    if ($result->num_rows) {
  ?>
      <div class="warning">
        <em>User exists. login</em>
      </div>
  <?php
    } else {
      runQuery($mysql, "insert into users_social10 (username, password, account_date) values (?, ?, utc_timestamp())", "ss", $username, $password);

      $sql = "
        select id from users_social10
        where username = ?
      ";
      $result = runSelectQuery($mysql, $sql, "s", $username);
      $_SESSION["userID"] = $result->fetch_assoc()["id"];
      header("location: postsFeed.php");
    }
    $mysql->close();
  }
  ?>
  <div class="signup-form">
    <form action="index.php" method="post">
      <div class="fields">
        <div class="input-field">
          <label for="">Username</label>
          <input type="text" name="username" id="username">
        </div>
        <div class="input-field">
          <label for="">Password</label>
          <input type="password" name="password" id="password">
        </div>
      </div>
      <button type="submit">Sign up</button>
    </form>
  </div>
  <?php require "footer.php" ?>
  <script src="auth.js"></script>
</body>

</html>