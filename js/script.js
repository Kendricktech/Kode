function menu_function() {
  let menubar = document.getElementById('menu-bar');
  let ac=document.getElementById('animatable-container');
  // Toggle display property
  if (menubar.style.display === 'grid' ) {
    menubar.style.display = 'none';
    ac.style.display='flex';
  } else {
    menubar.style.display = 'grid';
    ac.style.display='none';
  }
}