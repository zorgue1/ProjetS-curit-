const text = `Sois prêt à relever les défis des\n4 catégories ci-contre. \n\n
Attention jeune padawan, ce n'est pas de tout repos.\n\n
Les challenges sont divisés en trois niveaux \nde difficulté : easy, medium, hard.\n\n
Dès qu'un challenge sera réussi, ton score augmentera.`;

const textContainer = document.getElementById("animated-text");

let index = 0;

function typeText() {
  if (index < text.length) {
    textContainer.textContent += text[index];
    index++;
    setTimeout(typeText, 50); // Ajustez le délai pour la vitesse
  }
}

// Lance l'animation
typeText();
