// Variáveis globais e constantes
var url = "url.txt";
var portfolio = "https://a.a";
var isOnline = false;
var items = {};
const alwaysOnline = ['Contato', 'Github', 'Linkedin', 'Instagram'];

// Função para mostrar um toast
function showToast(text = "Carregando", icon = "", time = 5000) {
    let sound = false;
    switch (icon) {
        case "success":
            icon = "assets/success-icon.png";
            sound = "assets/fx-success.mp3";
            break;
        case "error":
            icon = "assets/error-icon.png";
            sound = "assets/fx-error.mp3";
            break;
        default:
            icon = false;
    }

    Toastify({
        text: text,
        duration: time,
        close: true,
        gravity: "top",
        position: "right",
        stopOnFocus: true,
        avatar: icon,
        style: {
            background: "#010C22",
            color: "#fff",
            borderRadius: "20px",
            border: "2px solid #55D0D7",
            boxShadow: "0 4px 8px rgba(0, 0, 0, 0.5)",
            padding: "1vh",
            fontFamily: "Pixel, sans-serif",
            textAlign: "center",
            textShadow: "2px 2px 4px rgba(0, 0, 0, 0.2)",
            fontSize: "0.8rem",
        }
    }).showToast();

    if (sound) {
        reproduzirSom(sound);
    }
}

// Função para reproduzir um som
function reproduzirSom(url) {
    let audio = new Audio(url);
    audio.play();

    audio.addEventListener('ended', function () {
        audio.pause();
        audio.currentTime = 0;
        audio.removeEventListener('ended', arguments.callee);
    });
}

// Função para ajudar a exibir um toast com texto de ajuda
function help_toast(data) {
    var help_text = data.getAttribute('help');
    showToast(help_text, "", 10000);
}

// Função para verificar mudanças nos objetos
function verificarMudanca(objetoAntigo, objetoNovo) {
    var time = 1000;
    for (let chave in objetoNovo) {
        if (objetoNovo.hasOwnProperty(chave)) {
            if (objetoAntigo[chave] !== objetoNovo[chave]) {
                console.log(chave + " está " + objetoNovo[chave]);
                if (objetoNovo[chave] == "online") {
                    showToast(chave.toUpperCase() + " FICOU ONLINE", "success", time);
                } else {
                    showToast(chave.toUpperCase() + " FICOU OFFLINE", "error", time);
                }
                time += 1000;
            }
        }
    }
}

// Função para observar mudanças nos objetos
function observarMudancas(objeto) {
    let objetoAntigo = Object.assign({}, objeto);

    setInterval(function () {
        verificarMudanca(objetoAntigo, objeto);
        objetoAntigo = Object.assign({}, objeto);
    }, 1000);
}

// Função para atualizar indicadores
function updateIndicators() {
    $('.indicator').each(function () {
        var cardTitle = $(this).closest('.card-body').find('.card-title').text().trim();
        if (alwaysOnline.includes(cardTitle)) {
            items[cardTitle] = "online";
            $(this).text('Online').removeClass('offline').addClass('online').fadeIn();
        } else {
            if (isOnline) {
                items[cardTitle] = "online";
                $(this).text('Online').removeClass('offline').addClass('online').fadeIn();
                $(this).closest('.card-body').find('.btn').fadeIn();
                if (cardTitle == "Portfólio") {
                    $(this).closest('.card-body').find('.btn').attr('href', portfolio);
                }
                if (cardTitle == "Loja") {
                    $(this).closest('.card-body').find('.btn').attr('href', portfolio + "/shop.php");
                }
                $(this).closest('.col-md-4').fadeIn();
            } else {
                $(this).text('Offline').removeClass('online').addClass('offline').fadeIn();
                $(this).closest('.card-body').find('.btn').fadeOut();
                $(this).closest('.col-md-4').fadeOut();
                items[cardTitle] = "offline";
            }
        }
    });
}

// Lazy Load
const lazyLoad = () => {
    const lazyElements = document.querySelectorAll('.lazy');
    lazyElements.forEach(element => {
        const rect = element.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            element.classList.add('loaded');
        }
    });
};

// Document ready
$(document).ready(function () {

    // Adiciona animação de delay em cada card
    $('.card').each(function (index) {
        var delay = (index + 1) * 0.2;
        $(this).css('animation-delay', delay + 's');
    });

    showToast("Bem vindo");

    // Evento de clique nas imagens com classe 'swal2-image'
    $(document).on('click', '.swal2-image', function (event) {
        const img = $(this);
        const offset = img.offset();
        const clickX = event.pageX - offset.left;
        const clickY = event.pageY - offset.top;

        if (img.hasClass('zoomed2')) {
            // Remove o zoom
            img.css({
                'transform': '',
                'transform-origin': ''
            });
            img.removeClass('zoomed2');
        } else {
            // Aplica o zoom
            const scale = 2;
            const originX = (clickX / img.width()) * 100;
            const originY = (clickY / img.height()) * 100;

            img.css({
                'transform': `scale(${scale})`,
                'transform-origin': `${originX}% ${originY}%`
            });
            img.addClass('zoomed2');
        }
    });

    // Evento de clique na imagem principal para exibir no Swal
    $('.main-image-container img').on("click", function () {
        Swal.fire({
            imageUrl: $(this).attr('src'),
            imageAlt: $(this).attr('alt'),
            imageWidth: '100%',
            imageHeight: '100%',
            footer: false,
            imageClassList: ['zoomable-image'],
            showConfirmButton: false,
            allowOutsideClick: true
        });
    });

    // Verifica se há um hash na URL e destaca o elemento correspondente
    if (window.location.hash) {
        var target = $(window.location.hash);

        if (target.length) {
            target.addClass('highlight');

            setTimeout(function () {
                target.removeClass('highlight');
            }, 3000);
        }
    }

    // Image Catch Error
    $('img').on('error', function () {
        $(this).attr('src', './assets/yourimage.jpeg');
    });

    window.addEventListener('scroll', lazyLoad);
    window.addEventListener('load', lazyLoad);



    // Inicializa o slick carousel para o slider
    $('.slider').slick({
        dots: true,
        arrows: true,
        infinite: true,
        speed: 300,
        slidesToShow: 1,
        slidesToScroll: 1,
        adaptiveHeight: true
    });

    // Clique nas miniaturas para mudar a imagem principal
    $('.thumbnail img').on('click', function () {
        var mainImage = $(this).closest('.slide').find('.main-image');
        var newSrc = $(this).data('main');
        mainImage.attr('src', newSrc);

        // Atualizar a classe ativa
        $(this).closest('.thumbnails').find('img').removeClass('active');
        $(this).addClass('active');
    });

    // Exibe imagem no Swal ao clicar nas miniaturas
    $('.thumbnail img').on("click", function () {
        Swal.fire({
            imageUrl: $(this).attr('src'),
            imageAlt: $(this).attr('alt'),
            imageWidth: '100%',
            imageHeight: '100%',
            footer: false,
            imageClassList: ['zoomable-image'],
            showConfirmButton: true,
            confirmButtonText: "Fechar",
            allowOutsideClick: true
        });
    });

    // Observa mudanças nos objetos
    observarMudancas(items);

    var texts = [
        "Bem vindo ao **LIMA HUB** 💟",
        "Esse é o portfolio da **Lima Webvision** 😉",
        "Criada por **Kauã Lima** 🧑‍💻",
        "Especializados em **Sistemas para Internet** 🛜",
        "Logo abaixo tem nossos **serviços favoritos** 🛠️",
        "Logo abaixo tem nossas **obras** 🎨",
        "Logo abaixo tem nossos **parceiros** 🤝",
        "É isso, já pode começar a ir para baixo ⬇️"
    ];

    var index = 0;

    function processText(text) {
        var processedText = [];
        var regex = /\*\*(.*?)\*\*/g;
        var match;

        while ((match = regex.exec(text)) !== null) {
            processedText.push({
                text: text.slice(0, match.index),
                bold: false
            });
            processedText.push({
                text: match[1],
                bold: true
            });
            text = text.slice(match.index + match[0].length);
        }

        if (text.length > 0) {
            processedText.push({
                text: text,
                bold: false
            });
        }

        return processedText;
    }

    var processedTexts = texts.map(processText);

    function showText() {
        var segment = processedTexts[index];
        $('#typing-text').empty(); // Limpa o conteúdo para a próxima animação

        segment.forEach(part => {
            if (part.bold) {
                $('#typing-text').append('<b class="marked-text">' + part.text + '</b>');
            } else {
                $('#typing-text').append(part.text);
            }
        });

        $('#typing-text').addClass('swipe-in');
        setTimeout(function () {
            $('#typing-text').removeClass('swipe-in').addClass('swipe-out');
        }, 3000); // Tempo antes de iniciar a saída (em milissegundos)

        setTimeout(function () {
            $('#typing-text').removeClass('swipe-out');
            index = (index + 1) % processedTexts.length;
            showText();
        }, 3500); // Tempo de espera antes de iniciar a próxima animação (em milissegundos)
    }

    if (window.innerWidth >= 600) {
        showText();
    } else {
        $('#typing-text').text("LIMA HUB");
    }

    var observerOptions = {
        root: null,
        rootMargin: "0px",
        threshold: 0.1
    };

    var observer = new IntersectionObserver(handleIntersection, observerOptions);

    var lazyElements = $('.lazy');
    lazyElements.each(function() {
        observer.observe(this);
    });

    function handleIntersection(entries, observer) {
        entries.forEach(function(entry) {
            var $element = $(entry.target);
            if (entry.isIntersecting) {
                if (!$element.hasClass('lazy-loaded')) {
                    $element.addClass('lazy-loaded');
                    var animations = ['lazy-animate-1', 'lazy-animate-2', 'lazy-animate-3'];
                    var animation = animations[Math.floor(Math.random() * animations.length)];
                    $element.addClass(animation).removeClass('lazy-out');
                }
            } else {
                if ($element.hasClass('lazy-loaded')) {
                    $element.removeClass('lazy-loaded lazy-animate-1 lazy-animate-2 lazy-animate-3').addClass('lazy-out');
                }
            }
        });
    }



});
