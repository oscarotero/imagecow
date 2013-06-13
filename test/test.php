<!doctype html>

<html>
	<head>
		<title>imageCow tests</title>
		<script src="../Imagecow/Imagecow.js" type="text/javascript" charset="utf-8"></script>

		<script type="text/javascript">
			window.Imagecow.init();
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
			document.getElementById('speed').innerHTML = Imagecow.getConnectionSpeed();
			document.getElementById('dimensions').innerHTML = Imagecow.getClientDimensions();
		</script>

		<?php
		$imgs = array();

		foreach (glob(__DIR__.'/pictures/*') as $picture) {
			$imgs[] = basename($picture);
		}

		$transforms = array(
			'resizeCrop,400,200;max-width=400:resize,200',
			//'resize,0,200',
			//'crop,400,200',
			//'resize,400,200|crop,500,100',
			//'resize,600,400|crop,100%,300,center,center',
			//'resizeCrop,200,200;max-width=400:resize,100'
		);
		?>

		<ul>
			<?php foreach ($transforms as $transform): ?>
			<?php foreach ($imgs as $img): ?>
			<li>
				<?php $src = "img.php?transform=$transform&amp;img=$img"; ?>
				<p><?php echo '<a href="pictures/'.$img.'">'.$img.'</a> /// <a href="'.$src.'">'.$transform.'</a>'; ?></p>
				<img src="<?php echo $src; ?>">
			</li>
			<?php endforeach; ?>
			<?php endforeach; ?>
		</ul>
	</body>
</html>