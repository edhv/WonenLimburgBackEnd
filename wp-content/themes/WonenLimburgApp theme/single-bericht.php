<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>WonenLimburg: <?php the_title() ?></title>
  <link href="http://mijnwonenlimburg.nl/app/wp-content/themes/WonenLimburgApp%20theme/app/css/fonts.css" />
  <style>
    body {
      font-family:'calibri';
      background-color: rgb(255, 255, 255);
      color: rgb(108, 108, 108);
    }

    .image img {
      border-radius: 8px;
    }

    .wrapper {
      background-color: rgb(236, 240, 236);
      width: 80vw;
      max-width: 600px;
      margin: 0 auto;
      padding: 1em 2em;
      border: 1px solid rgb(215, 226, 221);
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <img src="http://mijnwonenlimburg.nl/app/wp-content/themes/WonenLimburgApp%20theme/img/logo_WonenLimburg.webimage.gif" alt="Logo, Wonen Limburg">
  <div class="wrapper">
    <h1>
      <?php the_title(); ?>
    </h1>

    <article>
      <section class="image">

        <?php
          if (get_field('bericht_img')) {
            $source = get_field('bericht_img')['sizes']['team-landscape'];
            ?>
            <img src="<?php echo $source ?>" />
            <?php
          }
        ?>
      </section>
      <section class="text">
        <?php
          if (get_field('bericht_text')) {
            echo get_field('bericht_text');
          }
        ?>
    </section>
    </article>
  </div>
</body>
</html>
