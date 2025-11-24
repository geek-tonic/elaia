<?php

if (!defined('ABSPATH') || !defined('ELAIA_PLUGIN_DIR')) exit;

add_action('wp_enqueue_scripts', function () {

    // Langue courte (ex: "fr" depuis "fr-FR")
    $lang = substr(get_bloginfo('language'), 0, 2) ?: 'fr';

    // 1. Styles
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

    wp_enqueue_script(
        'elaia-chatbot',
        "https://chatbot.ela-ia.com/chatbot-v1.js?lang={$lang}&v=" . time(),
        [],
        false,
        ['strategy'  => 'defer']
    );
    // 2. Script distant
    wp_enqueue_script(
        'elaia-window',
        "https://chatbot.ela-ia.com/window-v1.js?lang={$lang}&v=" . time(),
        [],
        false,
        ['strategy'  => 'defer']
    );


    // 3. Script inline : gestion open/close
    wp_add_inline_script(
        'elaia-window',
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

        const IFRAME_ORIGIN = new URL(iframe.src).origin;

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

    // NOUVEAU : Ajouter la détection des formulaires séparément
    wp_add_inline_script(
        'elaia-chatbot',
        <<<JS
(function(){
    const TARGET_CLASSES = ['opened', 'toggled'];
    let formStates = new Map();

    const observeForm = (form, index) => {
        const formKey = form.id || 'form-' + index;
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
                            if (currentState) {
                                // Masquer le chatbot
                                const chatbot = document.getElementById('elaia-chatbot-button');
                                if (chatbot) chatbot.style.display = 'none';
                            } else {
                                // Réafficher le chatbot  
                                const chatbot = document.getElementById('elaia-chatbot-button');
                                if (chatbot) chatbot.style.display = 'flex';
                            }
                            formStates.get(form)[className] = currentState;
                        }
                    });
                }
            });
        });
        
        observer.observe(form, { attributes: true });
    };

    // Initialiser quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('form').forEach(observeForm);
        });
    } else {
        document.querySelectorAll('form').forEach(observeForm);
    }
})();
JS,
        'after'
    );
});
