import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('fishSearch', () => ({
    loading: false,

    startSearch() {
        this.loading = true;
    },
}));

Alpine.start();
