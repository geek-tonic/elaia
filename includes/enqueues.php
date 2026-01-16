<?php

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

add_action('wp_enqueue_scripts', function () {

    /**
     * 1) Styles
     */
    wp_register_style('elaia-window-style', false);
    wp_enqueue_style('elaia-window-style');
    wp_add_inline_style(
        'elaia-window-style',
        <<<CSS
.elaia-window-open{
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.3);
    z-index: 2147483647;
    width: 100%;
    max-width: 100%;
    margin:0;
    height: 100%;
    display: block;
}
.elaia-window-close{
    position: relative;
    width: 100%;
    max-width: 1200px;
    height: 100%;
    z-index: 0;
    margin: 0 auto;
}
CSS
    );

    /**
     * 2) Loader inline (détection langue côté DOM + injection des scripts distants AVEC lang=)
     *
     * - Supporte : espagnol, français, néerlandais, anglais, allemand, catalan, italien, portugais
     * - Déduit "de" depuis <html lang="de-DE">, ou depuis l'URL (/de/...)
     * - Fallback : en
     */
    wp_register_script('elaia-loader', false, [], null, true);
    wp_enqueue_script('elaia-loader');

    wp_add_inline_script(
        'elaia-loader',
        <<<JS
(function () {
  // Langues autorisées côté Elaia
  var ALLOWED = ['es','fr','nl','en','de','ca','it','pt'];

  function normalizeLang(input) {
    if (!input) return '';
    input = ('' + input).trim().toLowerCase();

    // "de-DE" => "de", "pt_BR" => "pt"
    if (input.indexOf('-') !== -1) input = input.split('-')[0];
    if (input.indexOf('_') !== -1) input = input.split('_')[0];

    return input;
  }

  function getLangFromHtml() {
    return normalizeLang(document.documentElement && document.documentElement.lang);
  }

  // Fallback URL : "/de/..." "/es/..." etc.
  function getLangFromPath() {
    var p = (location.pathname || '').toLowerCase();
    var match = p.match(/^\\/(es|fr|nl|en|de|ca|it|pt)(\\/|$)/);
    return match ? match[1] : '';
  }

  function getFinalLang() {
    var lang = getLangFromHtml() || getLangFromPath();
    if (ALLOWED.indexOf(lang) === -1) lang = 'en';
    return lang;
  }

  function loadScript(src) {
    var s = document.createElement('script');
    s.src = src;
    s.defer = true;
    document.head.appendChild(s);
  }

  var lang = getFinalLang();
  var v = Date.now();

  // IMPORTANT : on passe impérativement lang=
  loadScript('https://chatbot.ela-ia.com/chatbot-v1.js?lang=' + encodeURIComponent(lang) + '&v=' + v);
  loadScript('https://chatbot.ela-ia.com/window-v1.js?lang=' + encodeURIComponent(lang) + '&v=' + v);

})();
JS,
        'after'
    );

    /**
     * 3) Script inline : gestion open/close
     *    (accroché à elaia-loader puisque les scripts distants ne sont plus des handles WP)
     */
    wp_add_inline_script(
        'elaia-loader',
        <<<JS
(function(){
    const iframeReady = () => {
        const iframe = document.getElementById('elaia-window-iframe');
        return iframe && iframe.src;
    };

    const init = () => {
        const iframe = document.getElementById('elaia-window-iframe');
        const container = document.getElementById('elaia-window');
        if (!iframe || !container) return;

        let IFRAME_ORIGIN = '';
        try {
            IFRAME_ORIGIN = new URL(iframe.src).origin;
        } catch (e) {
            return;
        }

        window.addEventListener('message', (event) => {
            if (event.origin !== IFRAME_ORIGIN) return;
            if (event.source !== iframe.contentWindow) return;

            const data = event.data || {};
            switch (data.type) {
                case 'elaia:open':
                    document.querySelector('main')?.style.setProperty('z-index', 'unset');
                    container.classList.remove('elaia-window-close');
                    container.classList.add('elaia-window-open');
                    break;

                case 'elaia:close':
                    document.querySelector('main')?.style.removeProperty('z-index');
                    container.classList.remove('elaia-window-open');
                    container.classList.add('elaia-window-close');
                    break;

                case 'form:open':
                    // redimensionner l’iframe
                    iframe.style.height = '500px';
                    break;
            }
        }, false);
    };

    // attend que l’iframe soit injectée
    const observer = new MutationObserver(() => {
        if (iframeReady()) {
            observer.disconnect();
            init();
        }
    });

    observer.observe(document.body, { childList: true, subtree: true });
})();
JS,
        'after'
    );

    /**
     * 4) NOUVEAU : Détection des formulaires (masquer/afficher le bouton du chatbot)
     *    (accroché à elaia-loader)
     */
    wp_add_inline_script(
        'elaia-loader',
        <<<JS
(function(){
    const TARGET_CLASSES = ['opened', 'toggled'];
    let formStates = new Map();

    const observeForm = (form, index) => {
        const formKey = form.id || 'form-' + index; // gardé si tu l'utilises plus tard
        const initialState = {};

        TARGET_CLASSES.forEach(className => {
            initialState[className] = form.classList.contains(className);
        });
        formStates.set(form, initialState);

        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutation => {
                if (mutation.attributeName === "class") {
                    TARGET_CLASSES.forEach(className => {
                        const currentState = mutation.target.classList.contains(className);
                        const prevState = formStates.get(form)[className];

                        if (prevState !== currentState) {
                            const chatbot = document.getElementById('elaia-chatbot-button');
                            if (chatbot) {
                                chatbot.style.display = currentState ? 'none' : 'flex';
                            }
                            formStates.get(form)[className] = currentState;
                        }
                    });
                }
            });
        });

        observer.observe(form, { attributes: true });
    };

    const init = () => {
        document.querySelectorAll('form').forEach(observeForm);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
JS,
        'after'
    );
});
