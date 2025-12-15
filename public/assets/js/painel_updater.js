document.addEventListener("DOMContentLoaded", () => {
  const painelCarouselElement = document.getElementById("painelCarouselTV");
  if (!painelCarouselElement) return;

  const carouselInner = painelCarouselElement.querySelector(".carousel-inner");
  const campus = painelCarouselElement.dataset.campus;

  const baseUrl = painelCarouselElement.dataset.baseUrl;
  const apiUrl = `${baseUrl}api/painel-data/${campus}`;

  const UPDATE_INTERVAL = 5000; // 10 segundos para testes
  let currentDataSignature = "";

  // Referência para a instância do carrossel Bootstrap
  let carouselInstance = null;

  // Função para controlar a visibilidade do cabeçalho
  const toggleHeaderVisibility = () => {
    const infoHeader = document.getElementById("info-header");
    if (!infoHeader) return;

    // Atraso para garantir que o slide.bs.carousel já foi processado pelo Bootstrap
    setTimeout(() => {
      const activeItem = painelCarouselElement.querySelector(
        ".carousel-item.active"
      );
      if (activeItem && activeItem.classList.contains("publicidade-item")) {
        infoHeader.style.display = "none";
      } else {
        infoHeader.style.display = "block";
      }
    }, 50);
  };

  // Função para (re)inicializar o carrossel Bootstrap
  const initBootstrapCarousel = () => {
    // Destrói a instância existente se ela já existe
    if (carouselInstance) {
      carouselInstance.dispose();
    }

    carouselInstance = new bootstrap.Carousel(painelCarouselElement, {
      interval: 5000, // Um intervalo padrão para o carrossel principal (5 segundos)
      ride: "carousel", // Faz o carrossel iniciar automaticamente
    });
    // Adiciona o listener para a visibilidade do cabeçalho
    painelCarouselElement.addEventListener(
      "slid.bs.carousel",
      toggleHeaderVisibility
    );
    // Inicia o carrossel (ou o faz avançar para o primeiro slide se já estiver rodando)
    carouselInstance.to(0);
    carouselInstance.cycle();
    toggleHeaderVisibility();
  };

  const fetchAndUpdate = async () => {
    console.log("Buscando atualizações...", new Date().toLocaleTimeString());
    try {
      const response = await fetch(apiUrl);
      if (!response.ok) {
        console.error("Erro ao buscar dados da API. Status:", response.status);
        return;
      }
      const data = await response.json();

      const sortedPublicidades = [...data.publicidades].sort(
        (a, b) => a.id - b.id
      );
      const sortedReservas = [...data.reservas].sort((a, b) => a.id - b.id);

      const newDataSignature =
        JSON.stringify(sortedReservas) + JSON.stringify(sortedPublicidades);

      if (newDataSignature !== currentDataSignature) {
        console.log("Novos dados detectados. Atualizando o carrossel.");
        updateCarouselDOM(data.reservas, data.publicidades);
        currentDataSignature = newDataSignature;

        initBootstrapCarousel();
      } else {
        console.log("Nenhuma alteração nos dados.");
      }
    } catch (error) {
      console.error("Falha na conexão com a API.", error);
    }
  };

  const updateCarouselDOM = (reservas, publicidades) => {
    // 1. Limpar todos os slides existentes do carrossel
    while (carouselInner.firstChild) {
      carouselInner.removeChild(carouselInner.firstChild);
    }

    let isFirstItem = true; // Controla qual slide deve ter a classe 'active'
    const reservasPorSlide = 12;
    const publicidadeInterval = 15000; // 15 segundos
    const reservasInterval = 20000; // 20 segundos

    // --- Início da Lógica para Slides de Reservas ---
    if (reservas && reservas.length > 0) {
      for (let i = 0; i < reservas.length; i += reservasPorSlide) {
        const chunk = reservas.slice(i, i + reservasPorSlide);
        const slide = document.createElement("div");
        slide.classList.add("carousel-item");
        if (isFirstItem) {
          slide.classList.add("active");
          isFirstItem = false;
        }
        slide.setAttribute("data-bs-interval", reservasInterval.toString());

        const tableResponsive = document.createElement("div");
        tableResponsive.classList.add("table-responsive", "tabela");

        const table = document.createElement("table");
        table.classList.add("table", "table-striped");
        table.innerHTML = `
          <thead>
              <tr valign="middle" class="teste">
                  <th colspan="2" scope="col" class="border_radiu_start_1 text-center">HORÁRIOS</th>
                  <th scope="col" rowspan="2">CURSO / SETOR</th>
                  <th scope="col" rowspan="2">COMPONENTE CURRICULAR / ATIVIDADE</th>
                  <th scope="col" rowspan="2">MÓDULO</th>
                  <th scope="col" rowspan="2">PROFESSOR / SOLICITANTE</th>
                  <th rowspan="2" scope="col" class="border_radiu_end">SALA / LABORATÓRIO</th>
              </tr>
              <tr class="border_bottom">
                  <th scope="col" class="border_radiu_start_4">INÍCIO</th>
                  <th scope="col">FIM</th>
              </tr>
          </thead>
          <tbody>
          </tbody>
        `;

        const tbody = table.querySelector("tbody");
        chunk.forEach((row) => {
          const tr = document.createElement("tr");
          tr.setAttribute("valign", "middle");
          tr.innerHTML = `
            <td class="border_radiu_row_start">${
              row.res_hora_inicio_formatada
            }</td>
            <td>${row.res_hora_fim_formatada}</td>
            <td>${row.curs_curso || ""}</td>
            <td>${
              row.compc_componente ||
              row.res_componente_atividade_nome ||
              row.res_nome_atividade ||
              ""
            }</td>
            <td>${row.res_modulo || ""}</td>
            <td>${row.res_professor || ""}</td>
            <td class="border_radiu_row_end">${
              row.esp_nome_local_resumido || ""
            }</td>
          `;
          tbody.appendChild(tr);
        });

        tableResponsive.appendChild(table);
        slide.appendChild(tableResponsive);
        carouselInner.appendChild(slide);
      }
    } else {
      // Se NÃO HOUVER reservas, adiciona o slide de "Nenhuma programação"
      const slide = document.createElement("div");
      slide.classList.add("carousel-item");
      if (isFirstItem) {
        // Este slide será ativo se for o primeiro item geral
        slide.classList.add("active");
        isFirstItem = false;
      }
      slide.setAttribute("data-bs-interval", reservasInterval.toString());
      slide.innerHTML = `
            <div class="d-flex justify-content-center align-items-center" style="height: 50vh;">
                <h4>Nenhuma programação encontrada para hoje.</h4>
            </div>`;
      carouselInner.appendChild(slide);
    }
    // --- Fim da Lógica para Slides de Reservas ---

    // --- Início da Lógica para Slides de Publicidade ---
    // if (publicidades && publicidades.length > 0) {
    //   publicidades.forEach((pub) => {
    //     const slide = document.createElement("div");
    //     slide.classList.add("carousel-item", "publicidade-item");
    //     if (isFirstItem) {
    //       // Adiciona 'active' se ainda não houver nenhum slide ativo
    //       slide.classList.add("active");
    //       isFirstItem = false;
    //     }
    //     slide.setAttribute("data-bs-interval", publicidadeInterval.toString());

    if (publicidades && publicidades.length > 0) {
      publicidades.forEach((pub) => {
        const slide = document.createElement("div");
        slide.classList.add("carousel-item", "publicidade-item");
        if (isFirstItem) {
          slide.classList.add("active");
          isFirstItem = false;
        }

        // --- CORREÇÃO AQUI ---
        // Verifica se existe duração no objeto 'pub' vindo do banco.
        // Se existir e for maior que 0, multiplica por 1000. Se não, usa 15000.
        const tempoSlide = (pub.duracao && pub.duracao > 0) ? pub.duracao * 1000 : 10000;
        
        slide.setAttribute("data-bs-interval", tempoSlide.toString());

        const mediaUrl = `${baseUrl}public/${pub.caminho_imagem}`;

        if (pub.media_type === "video") {
          const videoType = pub.caminho_imagem.split(".").pop();
          slide.innerHTML = `
            <video class="d-block w-100 vh-100" style="object-fit: cover;" autoplay muted loop playsinline>
              <source src="${mediaUrl}" type="video/${videoType}">
              Seu navegador não suporta o formato de vídeo.
            </video>`;
        } else {
          slide.innerHTML = `
            <div class="img_public_dinamica vh-100" style="background-image: url('${mediaUrl}');"></div>`;
        }

        carouselInner.appendChild(slide);
      });
    }
    // --- Fim da Lógica para Slides de Publicidade ---

    // GARANTIA: Se, por algum motivo (ex: listas vazias), nenhum item ficou ativo,
    // garantimos que pelo menos o primeiro item do carrossel Bootstrap tem a classe 'active'.
    if (!carouselInner.querySelector(".carousel-item.active")) {
      const firstAddedSlide = carouselInner.querySelector(".carousel-item");
      if (firstAddedSlide) {
        firstAddedSlide.classList.add("active");
      }
    }
  };

  // Inicializa o carrossel Bootstrap e o polling APÓS a primeira carga dos dados
  // Chame initBootstrapCarousel ANTES de fetchAndUpdate para que carouselInstance seja definido
  initBootstrapCarousel();
  fetchAndUpdate(); // Faz a primeira busca e renderiza o carrossel
  setInterval(fetchAndUpdate, UPDATE_INTERVAL); // Inicia o polling
});
