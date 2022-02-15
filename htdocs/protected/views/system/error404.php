<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="keyword" content="">
    <title>404</title>
    <link rel="shortcut icon" href="/static/images/favicon.ico" type="image/x-icon">
    <!-- Bootstrap core CSS -->
<!--    <link href="/static/css/bootstrap.min.css" rel="stylesheet">-->
    <link href="/static/css/bootstrap-reset.css" rel="stylesheet">
    <!--external css-->
    <link href="/static/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <!-- Custom styles for this template -->
    <link href="/static/css/style.css" rel="stylesheet">
    <link href="/static/css/style-responsive.css" rel="stylesheet" />

    <script src="/static/js/jquery-1.8.3.min.js"></script>

    <style>

        .test .error-desk{
            display: none;
        }
        .test.body-404 {
            background: white;
        }
        .test .error-wrapper h1{
            display: none;        }
        .b-spinner {            opacity: 1;            position: absolute;        }
        #container{            text-align: center;        }
        #container.preloader.spinner {            background: none;        }
        #container .b-spinner {            left: 50%;            top: 50%;            width: 70px;            height: 70px;            overflow: hidden;        }
        #container .b-spinner .loader,
        #container .b-spinner .loader:after {
            border-radius: 50%;            width: 50px;            height: 50px;        }
        #container .b-spinner .loader {
            font-size: 6px;
            position: relative;
            text-indent: -9999em;
            border-top: 0.3em solid rgba(255, 255, 255, 0.2);
            border-right: 0.3em solid rgba(255, 255, 255, 0.2);
            border-bottom: 0.3em solid rgba(255, 255, 255, 0.2);            border-left: 0.3em solid #a48ad4;            -webkit-transform: translateZ;            -ms-transform: translateZ;            transform: translateZ;            -webkit-animation: load8 1.1s infinite linear;            animation: load8 1.1s infinite linear;        }        @-webkit-keyframes load8 {            0% {                -webkit-transform: rotate(0deg);                transform: rotate(0deg);            }            100% {                -webkit-transform: rotate(360deg);                transform: rotate(360deg);            }        }        @keyframes load8 {            0% {                -webkit-transform: rotate(0deg);                transform: rotate(0deg);            }            100% {                -webkit-transform: rotate(360deg);                transform: rotate(360deg);            }        }        #container .b-spinner .skype-loader {            width: 50px;            height: 50px;            margin: auto;            margin-top: -30px;            margin-left: -30px;            position: absolute;            left: 50%;            top: 50%;        }        #container .b-spinner .skype-loader .dot {            position: absolute;            top: 0;            left: 0;            width: 50px;            height: 50px;            animation: 1.7s dotrotate cubic-bezier(0.775, 0.005, 0.31, 1) infinite;        }        #container .b-spinner .skype-loader .dot:nth-child(1) {            animation-delay: 0.2s;        }        #container .b-spinner .skype-loader .dot:nth-child(2) {            animation-delay: 0.35s;        }        #container .b-spinner .skype-loader .dot:nth-child(3) {            animation-delay: 0.45s;        }        #container .b-spinner .skype-loader .dot:nth-child(4) {            animation-delay: 0.55s;        }        #container .b-spinner .skype-loader .dot:after,        #container .b-spinner .skype-loader .dot .first {            content: "";            position: absolute;            width: 6px;            height: 6px;            background: #a48ad4;            border-radius: 50%;            left: 50%;            margin-left: -4px;        }        #container .b-spinner .skype-loader .dot .first {            margin-top: -4px;            animation: 1.7s dotscale cubic-bezier(0.775, 0.005, 0.31, 1) infinite;            animation-delay: 0.2s;        }        @keyframes dotrotate {            from {                transform: rotate(0deg);            }            to {                transform: rotate(360deg);            }        }        @keyframes dotscale {            0%,            10% {                width: 12px;                height: 12px;                margin-left: -8px;                margin-top: -4px;            }            50% {                width: 8px;                height: 8px;                margin-left: -4px;                margin-top: 0;            }            90%,            100% {                width: 12px;                height: 12px;                margin-left: -8px;                margin-top: -4px;            }        }


    </style>

</head>
  <body class="body-404">
    <div class="error-head"> </div>
    <div class="container">
      <section class="error-wrapper text-center">
          <h1><img src="/static/images/404.png" alt=""></h1>
          <div class="error-desk">
              <h2><?php echo Yii::t('messages', 'the page not found', array(), null, Yii::app()->user->getState('language')); ?></h2>
              <p class="nrml-txt"><?php echo Yii::t('messages', 'We can not find this page', array(), null, Yii::app()->user->getState('language')); ?></p>
          </div>
          <a href="/" class="back-btn"><i class="fa fa-home"></i> <?php echo Yii::t('base', 'Back', array(), null, Yii::app()->user->getState('language')); ?></a>
      </section>
    </div>

    <div class="container bottom hide">
        <div class="b-spinner">
            <div class="loader"></div>
        </div>
    </div>

  </body>
</html>


<script>
    $(function() {
        if (location.hash == '#ru') {
            $('.container.bottom').attr('id','container').removeClass('hide');
            $('body').addClass('test');
        }
    });
</script>