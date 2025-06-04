document.addEventListener("DOMContentLoaded", function () {
    let menuItems = document.querySelectorAll(".btn-sideBar-SubMenu");

    menuItems.forEach(item => {
        item.addEventListener("click", function () {
            let submenu = this.nextElementSibling;

            // Cierra todos los demás submenús
            document.querySelectorAll(".submenu").forEach(sub => {
                if (sub !== submenu) {
                    sub.style.display = "none";
                }
            });

            // Alterna la visibilidad del submenú seleccionado
            submenu.style.display = submenu.style.display === "block" ? "none" : "block";
        });
    });
});
