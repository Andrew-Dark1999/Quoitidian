<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keyword" content="">
    <link rel="shortcut icon" href="/static/images/favicon.ico" type="image/x-icon">

    <title>500</title>


    <!-- Bootstrap core CSS -->
    <link href="/static/css/bootstrap.min.css" rel="stylesheet">
    <link href="/static/css/bootstrap-reset.css" rel="stylesheet">
    <!--external css-->
    <link href="font-awesome/css/font-awesome.css" rel="stylesheet" />
    <!-- Custom styles for this template -->
    <link href="/static/css/style.css" rel="stylesheet">
    <link href="/static/css/style-responsive.css" rel="stylesheet" />
</head>




  <body class="body-500">

    <div class="error-head"> </div>

    <div class="container ">

      <section class="error-wrapper text-center">
          <h1><img src="/static/images/500.png" alt=""></h1>
          <div class="error-desk">
              <h2>Вот незадача!!!</h2>
              <p class="nrml-txt-alt"><?php echo Yii::t('messages', 'Something went wrong.'); ?></p>
              <p><?php echo Yii::t('messages', 'Try refreshing the page, or you can'); ?> <a href="/" class="sp-link"><?php echo Yii::t('messages', 'contact tech support'); ?></a>, <?php echo Yii::t('messages', 'if the problem persists.'); ?></p>
          </div>
          <a href="/" class="back-btn"><i class="fa fa-home"></i> <?php echo Yii::t('base', 'Back'); ?></a>
      </section>

    </div>


  </body>
</html>
