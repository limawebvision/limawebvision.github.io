  // Função para trocar o tema
  function trocaTema(tema) {
    var linkTema = $("#cssbase");
    switch(tema) {
      case 'gamer':
        linkTema.attr("href", "./gamer.css");
        break;
      case 'minimalist':
        linkTema.attr("href", "./minimal.css");
        break;
      case 'dark':
        linkTema.attr("href", "./dark.css");
        break;
      case 'redfall':
        linkTema.attr("href", "./redfall.css");
        break;
    }
  }

  $(document).ready(function() {
    Swal.fire({
      title: 'Dica do Dia 1',
      text: 'Clique duas vezes rapidamente em algum texto para edita-lo e ver como seria do seu jeito'
    }).then((result) => {
      Swal.fire({
        title: 'Dica do Dia 2',
        text: 'Ali em cima, clique na opção temas e escolha um diferente caso queira'
      });
    });

    $('span, p, h1, h2, h3, h4, h5').dblclick(function() {
      var currentElement = $(this);
      var currentText = currentElement.text();

      Swal.fire({
        title: 'Editar texto',
        input: 'text',
        inputLabel: 'Novo texto',
        inputValue: currentText,
        showCancelButton: true,
        inputValidator: (value) => {
          if (!value) {
            return 'Você precisa digitar algum texto!';
          }
        }
      }).then((result) => {
        if (result.isConfirmed) {
          currentElement.text(result.value);
          Swal.fire(
            'Atualizado!',
            'O texto foi atualizado.',
            'success'
          );
        }
      });
    });

    $(".dropdown-item").click(function() {
      var tema = $(this).data("theme");
      trocaTema(tema);
    });

    // Lazy loading para textos
    const lazyLoad = () => {
      const lazyElements = document.querySelectorAll('.lazy');
      lazyElements.forEach(element => {
        const rect = element.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
          element.classList.add('loaded');
        }
      });
    };

    window.addEventListener('scroll', lazyLoad);
    window.addEventListener('load', lazyLoad);

    setTimeout(function(){
      Swal.fire({
        title: 'Opa, eai, como vai?',
        confirmButtonText: "Com certeza",
        showCancelButton: true,
        text: 'To vendo que se ta navegando bastante, que tal agendar uma reunião conosco para ter sua propria pagina?'
      }).then((result) => {
        if (result.isConfirmed){
          window.location.href ="/index.html#contato";
        }
      });
    }, 30000);
  });