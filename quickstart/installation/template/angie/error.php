<?php
/**
 * @package angi4j
 * @copyright Copyright (c)2009-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author Nicholas K. Dionysopoulos - http://www.dionysopoulos.me
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

defined('_AKEEBA') or die();

?>
<html>
<head>
	<title>ANGIE - Akeeba Next Generation Installation Engine v. <?php echo AKEEBA_VERSION ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script type="text/javascript" src="template/angie/js/jquery.js"></script>
	<script type="text/javascript" src="template/angie/js/jquery.simulate.js"></script>
	<script type="text/javascript" src="template/angie/js/bootstrap.min.js"></script>
	<link rel="stylesheet" type="text/css" href="template/angie/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="template/angie/css/bootstrap-responsive.min.css" />
	<link rel="stylesheet" type="text/css" href="template/angie/css/footer.css" />
</head>
<body>
	<div id="wrap">
		<div class="navbar navbar-inverse navbar-static-top">
			<div class="navbar-inner">
				<div class="container">
					<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="brand" href="#">ANGIE – Akeeba Next Generation Installer Engine v.<?php echo AKEEBA_VERSION ?></a>
				</div>
			</div>
		</div>
		<div class="container">
			<?php echo $error_message ?>
		</div>
		<div id="footer">
			<div class="container">
				<p class="muted credit">
					Copyright &copy;2006 &ndash; <?php echo date('Y') ?> Akeeba Ltd. All rights reserved.<br/>
					ANGIE is Free Software distributed under the
					<a href="http://www.gnu.org/licenses/gpl.html">GNU GPL version 3</a> or any later version published by the FSF.
				</p>
			</div>
		</div>
	</div>
</body>
</html>
