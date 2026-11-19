const daftarMenu = [
    {
        nama: "Dashboard",
        img: "assets/dashboard-671-svgrepo-com.svg",
        href: "index.html"
    },
    {
        nama: "Inventory",
        img: "assets/inventorymajor-svgrepo-com.svg",
        href: "inventory.html"
    },
    {
        nama: "Pengiriman",
        img: "assets/supply-chain-inventory-manufacturing-supply-svgrepo-com.svg",
        href: "pengiriman.html"
    },
    {
        nama: "Management Toko",
        img: "assets/store-svgrepo-com.svg",
        href: "manajemen_toko.html"
    }
];

// Fungsi Utama: Memuat layout sidebar secara eksternal (Include System)
function loadSidebar() {
    const sidebarContainer = document.getElementById('sidebar-container');
    if (!sidebarContainer) return;

    fetch('sidebar.html')
        .then(response => {
            if (!response.ok) throw new Error("Gagal mengambil file sidebar.html");
            return response.text();
        })
        .then(htmlData => {
            // Suntik isi HTML sidebar
            sidebarContainer.innerHTML = htmlData;

            // Jalankan fungsionalitas sidebar setelah komponennya sukses dimuat
            renderMenuLinks();
            initSidebarToggle();
        })
        .catch(error => console.error("Error Templating:", error));
}

// Fungsi me-looping menu ke dalam sidebar
function renderMenuLinks() {
    const menuContainer = document.querySelector('.menu');
    if (!menuContainer) return;

    // Ambil nama file halaman saat ini (misal: 'index.html' atau 'lowongan.html')
    const currentPath = window.location.pathname.split("/").pop() || "index.html";

    const menuHTML = daftarMenu.map(item => {
        // Cek jika href menu sama dengan halaman saat ini, berikan penanda visual jika lo mau styling nanti
        const isActive = currentPath === item.href ? 'active-menu' : '';

        return `
            <button class="in-menu ${isActive}">
                <a href="${item.href}">
                    <img src="${item.img}" alt="${item.nama}">
                    <p>${item.nama}</p>
                </a>
            </button>
        `;
    }).join('');

    const menuTitle = menuContainer.querySelector('p');
    menuTitle.insertAdjacentHTML('afterend', menuHTML);
}

// Fungsi mengontrol buka-tutup sidebar
function initSidebarToggle() {
    const outDash = document.querySelector('.out-dash');
    const buttonDash = document.querySelector('.button-dash');

    if (!buttonDash || !outDash) return;

    buttonDash.addEventListener('click', () => {
        outDash.classList.toggle('closed');
        
        if (outDash.classList.contains('closed')) {
            buttonDash.style.transform = 'rotate(180deg)';
        } else {
            buttonDash.style.transform = 'rotate(0deg)';
        }
    });
}

// Jalankan sistem saat file HTML utama siap dimuat
document.addEventListener('DOMContentLoaded', loadSidebar);