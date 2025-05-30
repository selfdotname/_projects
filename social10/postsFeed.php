<!DOCTYPE html>
<html lang="en">

<head>
  <?php require "page/head.php" ?>
  <style>
    @import url(css/navLinks.css);
    @import url(css/titles.css);
    @import url(css/form.css);
    @import url(css/posts.css);
    @import url(css/modal.css);
    @import url(css/footer.css);


    body {
      display: flex;
      flex-direction: column;
      overflow: hidden;
      height: 100dvh;
    }
  </style>
</head>

<body>
  <?php session_start(); ?>
  <div class="title--social10">
    <h1>Social10</h1>
  </div>
  <div class="nav-links nav-links--posts-page">
    <nav>
      <a href="postsFeed.php" class="active-link">Posts</a> |
      <?php if (isset($_SESSION["userID"])) { ?>
        <a href="logout.php">Log out</a>
      <?php } else { ?>
        <a href="index.php">Sign up</a> |
        <a href="login.php">Log in</a>
      <?php } ?>
    </nav>
  </div>
  <?php
  require "actions.php";
  if (isset($_SESSION["userID"])) {
    $userID = (int) $_SESSION["userID"];
    $mysql = connectToMySQL();
    $result = runSelectQuery($mysql, "select username from users_social10 where id = ?", "i", $userID);
    $username = $result->fetch_assoc()["username"];
  ?>
    <div class="title--welcome-message">
      <h2>Welcome, <?= htmlspecialchars($username) ?></h2>
      <div class="post-button">
        <button>
          <i class="material-icons">add</i>
          Make post
        </button>
      </div>
    </div>
  <?php
  }
  ?>
  <div class="title--posts">
    <h2>Posts</h2>
  </div>
  <?php
  // comment if any
  if (isset($_POST["comment"]) && isset($_POST["postID"])) {
    $comment = $_POST["comment"];
    $postID = $_POST["postID"];

    // make sure post exists
    $sql = "
    select * from posts_social10
    where id = ?
    ";
    $result = runSelectQuery($mysql, $sql, "i", $postID);
    $postExists = $result->num_rows ? true : false;

    if ($postExists) {
      // comment
      $sql = "
          insert into comments_social10 (commenter_id, post_id, comment, comment_date)
          values (?, ?, ?, utc_timestamp())
          ";
      $affectedRow = runQuery($mysql, $sql, "iis", $userID, $postID, $comment);
      if ($affectedRow) {
  ?>
        <script>
          alert("Comment posted");
          window.location.href = "postsFeed.php"
        </script>
      <?php
      }
    } else {
      ?>
      <script>
        alert("Cannot comment, post has been deleted.");
        window.location.href = "postsFeed.php"
      </script>
  <?php
    }
  }
  ?>
  <div class="posts">
    <?php
    // create post if any
    if (isset($_POST["content"])) {
      $content = $_POST["content"];
      $sql = "
        insert into posts_social10 (author_id, content, post_date)
        values (?, ?, utc_timestamp())
      ";
      $affectedRow = runQuery($mysql, $sql, "is", $userID, $content);
      if ($affectedRow) {
    ?>
        <script>
          alert("Post sent!")
          window.location.href = "postsFeed.php"
        </script>
        <?php
      }
    }
    // delete post if any
    if (isset($_POST["postID"]) && count($_POST) == 1) {
      //make sure user owns the post 
      $postID = (int) $_POST["postID"];
      $sql = "
        select author_id from posts_social10
        where id = ?
      ";
      $result = runSelectQuery($mysql, $sql, "i", $postID);
      $authorID = (int) $result->fetch_assoc()["author_id"];

      if ($userID == $authorID) {
        // delete comments dependencies first
        $sql = "
        delete from comments_social10
        where post_id = ?
      ";
        runQuery($mysql, $sql, "i", $postID);

        // delete likes dependencies first
        $sql = "
        delete from likes_social10
        where post_id = ?
      ";
        runQuery($mysql, $sql, "i", $postID);

        // delete post
        $sql = "
        delete from posts_social10
        where id = ?
      ";
        $affectedRow = runQuery($mysql, $sql, "i", $postID);
        if ($affectedRow > 0) {
        ?>
          <script>
            alert("Post deleted")
            window.location.href = "postsFeed.php"
          </script>
        <?php
        }
      }
    }
    // render all posts
    $sql = "
    select posts_social10.id as post_id, posts_social10.author_id as author_id, username, content, post_date
    from  users_social10
    inner join posts_social10
    on  users_social10.id = posts_social10.author_id
    order by post_date desc
  ";
    $mysql = connectToMySQL();
    $result = runSelectQuery($mysql, $sql);
    $posts = $result->num_rows ? $result->fetch_all(MYSQLI_ASSOC) : null;
    if ($posts) {
      foreach ($posts as $post) {
        ?>
        <div class="post">
          <div class="post-head">
            <div class="delete-post-form-wrapper">
              <h3><?= htmlspecialchars($post["username"]) ?></h3>
              <?php
              if (isset($userID) && $userID == $post["author_id"]) {
              ?>
                <div class="delete-post-form">
                  <form action="postsFeed.php" method="post">
                    <input type="hidden" name="postID" value="<?= (int) $post["post_id"] ?>">
                    <button>
                      <i class="material-icons" style="color: red;font-size: 1.5rem;">delete</i>
                    </button>
                  </form>
                </div>
              <?php
              }
              ?>
            </div>
            <small><?= htmlspecialchars($post["post_date"]) ?></small>
          </div>
          <div class="post-content">
            <p><?= htmlspecialchars($post["content"]) ?></p>
          </div>
          <div class="engagements-group">
            <div class="engagements">
              <button data-post-id="<?= (int) $post["post_id"] ?>" class="likeButton">
                <?php
                // fetch likes
                $sql = "
                select count(id) as total_likes
                from likes_social10
                where post_id = ? 
              ";
                $result = runSelectQuery($mysql, $sql, "i", (int) $post["post_id"]);
                $totalLikes = $result->fetch_assoc()["total_likes"];
                ?>
                <span id="likeCounter<?= (int) $post["post_id"] ?>"><?= $totalLikes ?></span>
                <?php
                // have user liked post
                if (isset($_SESSION["userID"])) {
                  $sql = "
                  select *
                  from likes_social10
                  where post_id = ? 
                  and liker_id = ?
                ";
                  $result = runSelectQuery($mysql, $sql, "ii", (int) $post["post_id"], (int) $userID);
                  $haveLiked = $result->num_rows ? true : false;
                  if ($haveLiked) {
                ?>
                    <i class="material-icons" style="color: red;" id="likeButton<?= (int) $post["post_id"] ?>">favorite</i>

                  <?php
                  } else {
                  ?>
                    <i class="material-icons" id="likeButton<?= (int) $post["post_id"] ?>">favorite_outline</i>
                  <?Php
                  }
                } else {
                  ?>
                  <i class="material-icons">favorite_outline</i>
                <?php
                }
                ?>
              </button>
            </div>
            <div class="engagements">
              <button data-post-id="<?= (int) $post["post_id"] ?>" class="commentButton">
                <?php
                // fetch likes
                $sql = "
                select count(id) as total_comments
                from comments_social10
                where post_id = ? 
              ";
                $result = runSelectQuery($mysql, $sql, "i", (int) $post["post_id"]);
                $totalComments = $result->fetch_assoc()["total_comments"];
                ?>
                <span><?= $totalComments ?></span>
                <i class="material-icons">comment</i>
              </button>
            </div>
            <span class="view-posts">
              <a href="post.php?id=<?= (int) $post["post_id"] ?>">View post</a>
            </span>
          </div>
          <div class="comment-form">
            <?php
            if (isset($_SESSION["userID"])) {
            ?>
              <form action="postsFeed.php" method="post" data-post-Id="<?= (int) $post["post_id"] ?>">
                <input type="text" name="comment" placeholder="Drop a comment" id="commentInput<?= (int) $post["post_id"] ?>">
                <input type="hidden" name="postID" value="<?= (int) $post["post_id"] ?>">
                <button type="submit">Comment</button>
              </form>
            <?php
            }
            ?>
          </div>
        </div>
      <?php
      }
    } else {
      ?>
      <div class="title--no-posts-postsfeed">
        <h3>A little quiet no?</h3>
        <p>Hit the make post button to make some noise</p>
      </div>
    <?php
    }
    ?>
    <div class="modal-background hidden">
      <div class="modal">
        <div class="modal-head">
          <h3>Username</h3>
          <i class="material-icons">close</i>
        </div>
        <div class="post-form">
          <form action="postsFeed.php" method="post">
            <textarea name="content" placeholder="Whats happening?"></textarea>
            <button>
              Post
              <i class="material-icons">north_east</i>
            </button>
          </form>
        </div>
      </div>
    </div>
    <?php
    $mysql->close();
    ?>
  </div>
  <?php require "footer.php" ?>
  <script src="like.js"></script>
  <script src="comment.js"></script>
  <script src="time.js"></script>
  <script src="makePost.js"></script>
  <script src="warnBeforeDelete.js"></script>
</body>

</html>