

/**
 *
 * @copyright  2010-2012 izend.org
 * @version    10
 * @link       http://www.izend.org
 */

<?php
$aliases = array(
	array(
		'captcha'			=> 'captcha',
		'avatar'			=> 'avatar',
		'logo'				=> 'logo',
		'rssfeed'			=> 'rss',
		'paypalreturn'		=> 'paypal/return',
		'paypalcancel'		=> 'paypal/cancel',
	),
	'en' 	=> array(
		'install'			=> 'install',
		'homepage'			=> 'home',
		'home'				=> 'home',
		'contact'			=> 'contact',
		'user'				=> 'user',
		'nobody'			=> 'disconnect',
		'account'			=> 'account',
		'password'			=> 'password',
		'newuser'			=> 'new-user',
		'search'			=> 'search',
		'suggest'			=> 'suggest',
		'download'			=> 'download',
		'donation'			=> 'donation',
		'admin'				=> 'manage',
		'adminuser'			=> 'manage/user',
		'page'				=> 'page',
		'editpage'			=> 'edit',
		'folder'			=> 'folder',
		'folderedit'		=> 'edit/folder',
		'story'				=> 'article',
		'storyedit'			=> 'edit/article',
		'book'				=> 'manual',
		'bookedit'			=> 'edit/manual',
		'newsletter'		=> 'newsletter',
		'newsletteredit'	=> 'edit/newsletter',
		'newsletteruser' 	=> 'newsletter/subscribe',
		'thread'			=> 'thread',
		'threadedit'		=> 'edit/thread',
		'node'				=> 'node',
		'editnode'			=> 'edit/node',
		'pagecontent'		=> 'content',
	),
	'fr' 	=> array(
		'install'			=> 'installation',
		'homepage'			=> 'accueil',
		'home'				=> 'accueil',
		'contact'			=> 'contact',
		'user'				=> 'utilisateur',
		'nobody'			=> 'deconnexion',
		'account'			=> 'compte',
		'password'			=> 'mot-de-passe',
		'newuser'			=> 'nouvel-utilisateur',
		'search'			=> 'recherche',
		'suggest'			=> 'suggestion',
		'download'			=> 'telechargement',
		'donation'			=> 'donation',
		'admin'				=> 'gestion',
		'adminuser'			=> 'gestion/utilisateur',
		'page'				=> 'page',
		'editpage'			=> 'edition',
		'folder'			=> 'dossier',
		'folderedit'		=> 'edit/dossier',
		'story'				=> 'article',
		'storyedit'			=> 'edition/article',
		'book'				=> 'manuel',
		'bookedit'			=> 'edition/manuel',
		'newsletter'		=> 'infolettre',
		'newsletteredit'	=> 'edition/infolettre',
		'newsletteruser' 	=> 'infolettre/abonnement',
		'thread'			=> 'fil',
		'threadedit'		=> 'edition/fil',
		'node'				=> 'noeud',
		'editnode'			=> 'edition/noeud',
		'pagecontent'		=> 'contenu',
	),
);
?>
$aliases = array(
	array(
<?php foreach ($aliases[0] as $action => $path): ?>
<?php if (in_array($action, $features)): ?>
		'<?php echo $path; ?>' => '<?php echo $action; ?>',
<?php endif; ?>
<?php endforeach; ?>
	),
<?php foreach ($languages as $lang): ?>
	'<?php echo $lang; ?>' => array(
<?php foreach ($aliases[$lang] as $action => $path): ?>
<?php if (in_array($action, $features)): ?>
		'<?php echo $path; ?>' => '<?php echo $action; ?>',
<?php endif; ?>
<?php endforeach; ?>
	),
<?php endforeach; ?>
);

