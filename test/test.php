<?php
include('loader.php');

use Fol\Loader;

Loader::setLibrariesPath(dirname(__DIR__));
Loader::register();

$Image = Imagecow\Image::create();
?>

<!doctype html>

<html>
	<head>
		<title>imageCow tests</title>
		<script src="imageCow.js" type="text/javascript" charset="utf-8"></script>

		<script type="text/javascript">
			window.imageCow.init();
		</script>

		<style type="text/css" media="screen">
			html {
				background: #666;
			}

			ul {
				list-style: none;
				margin: 0;
			}

			li {
				margin: 20px 0;
				background: #FFFF99;
				padding: 10px;
			}
			img {
				box-shadow: 0 0 4px black;
			}
		</style>
	</head>

	<body>
		<p>speed: <span id="speed"></span> | dimensions: <span id="dimensions"></span></p>
		<script type="text/javascript">
			document.getElementById('speed').innerHTML = imageCow.getConnectionSpeed();
			document.getElementById('dimensions').innerHTML = imageCow.getClientDimensions();
		</script>

		<?php
		$imgs = array(
			'img-8.png', 
			'img.png', 
			'img.gif', 
			'img-a.gif', 
			'img.jpg'
		);
		$transforms = array(
			'resize,600,400|crop,100%,300,center,center',
			/*
			'crop,200,200|resize,400',
			'crop,200,200|resize,400,0,0',
			'crop,200,200|resize,400,400,1',
			'zoomCrop,800,500',
			*/
		);
		?>

		<ul>
			<?php foreach ($transforms as $transform): ?>
			<?php foreach ($imgs as $img): ?>
			<li>
				<?php $src = "img.php?transform=$transform&amp;img=$img"; ?>
				<p><?php echo '<a href="'.$img.'">'.$img.'</a> /// <a href="'.$src.'">'.$transform.'</a>'; ?></p>
				<img src="<?php echo $src; ?>">
			</li>
			<?php endforeach; ?>
			<?php endforeach; ?>
		</ul>
	</body>
</html>