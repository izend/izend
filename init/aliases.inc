

/**
 *
 * @copyright  2010-2011 izend.org
 * @version    5
 * @link       http://www.izend.org
 */

<?php
$aliases = array(
	array(
		'captcha'		=> 'captcha',
		'avatar'		=> 'avatar',
		'logo'			=> 'logo',
		'rssfeed'		=> 'rss',
	),
	'en' => array(
		'install'		=> 'install',
		'home'			=> 'home',
		'homepage'		=> 'home',
		'contact'		=> 'contact',
		'user'			=> 'user',
		'nobody'		=> 'disconnect',
		'account'		=> 'account',
		'password'		=> 'password',
		'newuser'		=> 'new-user',
		'search'		=> 'search',
		'suggest'		=> 'suggest',
		'download'		=> 'download',
		'admin'			=> 'manage',
		'adminuser'		=> 'manage/user',
		'page'			=> 'page',
		'editpage'		=> 'edit',
		'folder'		=> 'folder',
		'folderedit'	=> 'edit/folder',
		'story'			=> 'article',
		'storyedit'		=> 'edit/article',
		'book'			=> 'manual',
		'bookedit'		=> 'edit/manual',
		'thread'		=> 'thread',
		'threadedit'	=> 'edit/thread',
		'node'			=> 'node',
		'editnode'		=> 'edit/node',
	),
	'fr' => array(
		'install'		=> 'installation',
		'homepage'		=> 'accueil',
		'home'			=> 'accueil',
		'contact'		=> 'contact',
		'user'			=> 'utilisateur',
		'nobody'		=> 'déconnexion',
		'account'		=> 'compte',
		'password'		=> 'mot-de-passe',
		'newuser'		=> 'nouvel-utilisateur',
		'search'		=> 'recherche',
		'suggest'		=> 'suggestion',
		'download'		=> 'telechargement',
		'admin'			=> 'gestion',
		'adminuser'		=> 'gestion/utilisateur',
		'page'			=> 'page',
		'editpage'		=> 'edition',
		'folder'		=> 'dossier',
		'folderedit'	=> 'edit/dossier',
		'story'			=> 'article',
		'storyedit'		=> 'edition/article',
		'book'			=> 'manuel',
		'bookedit'		=> 'edition/manuel',
		'thread'		=> 'fil',
		'threadedit'	=> 'edition/fil',
		'node'			=> 'noeud',
		'editnode'		=> 'edition/noeud',
	),
);
?>
$aliases = array(
	array(
<?php foreach ($aliases[0] as $action => $path): ?>
<?php if (in_array($action, $features)): ?>
		'<?php echo $path; ?>'	=> '<?php echo $action; ?>',
<?php endif; ?>
<?php endforeach; ?>
	),
<?php foreach ($languages as $lang): ?>
	'<?php echo $lang; ?>' => array(
<?php foreach ($aliases[$lang] as $action => $path): ?>
<?php if (in_array($action, $features)): ?>
		'<?php echo $path; ?>'	=> '<?php echo $action; ?>',
<?php endif; ?>
<?php endforeach; ?>
	),
<?php endforeach; ?>
);

