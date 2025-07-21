// document.addEventListener("DOMContentLoaded", function () {
//   const carousel = document.getElementById("painelCarouselTV");
//   const infoHeader = document.getElementById("info-header");

//   if (!carousel || !infoHeader) {
//     console.error(
//       "Elementos essenciais (carrossel ou cabeçalho) não encontrados."
//     );
//     return;
//   }

//   const toggleHeaderVisibility = () => {
//     const activeItem = carousel.querySelector(".carousel-item.active");
//     if (activeItem && activeItem.classList.contains("publicidade-item")) {
//       infoHeader.style.display = "none";
//     } else {
//       infoHeader.style.display = "block";
//     }
//   };

//   carousel.addEventListener("slid.bs.carousel", toggleHeaderVisibility);

//   toggleHeaderVisibility();
// });

document.addEventListener("DOMContentLoaded", function () {
  const carousel = document.getElementById("painelCarouselTV");
  const infoHeader = document.getElementById("info-header");

  if (!carousel || !infoHeader) {
    console.error(
      "Elementos essenciais (carrossel ou cabeçalho) não encontrados."
    );
    return;
  }

  const toggleHeaderVisibility = () => {
    const activeItem = carousel.querySelector(".carousel-item.active");
    if (activeItem && activeItem.classList.contains("publicidade-item")) {
      infoHeader.style.display = "none";
    } else {
      infoHeader.style.display = "block";
    }
  };

  carousel.addEventListener("slid.bs.carousel", toggleHeaderVisibility);

  // A chamada inicial já está correta
  toggleHeaderVisibility();
});
