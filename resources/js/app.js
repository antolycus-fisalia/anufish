import Alpine from 'alpinejs';
import {
    initNavigation,
    initPasswordFields,
    initForms,
    initProfile,
    initFishCardImages,
} from './ui.js';
import { initFishDetails } from './fish-detail.js';
import { initAuthLayout } from './auth-layout.js';
import { scanUpload } from './scan.js';

window.Alpine = Alpine;
Alpine.data('scanUpload', scanUpload);

initNavigation();
initPasswordFields();
initForms();
initProfile();
initFishDetails();
initAuthLayout();
initFishCardImages();

Alpine.start();
