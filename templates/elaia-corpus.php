<?php if (!defined('ABSPATH')) exit;
show_admin_bar(false);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
</head>
<body style="margin:0;padding:0;background:#f5f5f5;">
<?php
    global $elaia_corpus_domain, $post;
    if ($post && preg_match('/domain="([^"]+)"/', $post->post_content, $m)) {
        $elaia_corpus_domain = $m[1];
    }
    include_once ELAIA_PLUGIN_DIR . 'includes/Pages/Corpus.php';
    elaia_prepare_corpus_payload();
?>
</body>
</html>