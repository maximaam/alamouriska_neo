const container = document.querySelector('.vich-image');

if (container) {
  const a = container.querySelector('a');
  const img = a?.querySelector('img');

  if (a && img) {
    img.setAttribute('width', '128');
    img.setAttribute('height', '128');

    // Move the img out of the <a> and into the container
    container.insertBefore(img, a);
    a.remove();
  }
}



/*
document.addEventListener('DOMContentLoaded', () => {
  document.querySelector('.vich-image a')?.remove();
});
*/