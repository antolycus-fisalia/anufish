import Alpine from 'alpinejs';
import {
    initNavigation,
    initPasswordFields,
    initForms,
    initProfile,
    initImageFallbacks,
    initInvalidFieldFocus,
} from './ui.js';
import { initFishDetails } from './fish-detail.js';
import { initAuthLayout } from './auth-layout.js';
import { scanUpload } from './scan.js';
import { initHistoryImages } from './scan-history.js';
import { initArticleList } from './article-list.js';
import { initArticleForm } from './article-form.js';

window.Alpine = Alpine;

Alpine.data('scanUpload', scanUpload);

initNavigation();
initPasswordFields();
initForms();
initProfile();
initFishDetails();
initAuthLayout();
initImageFallbacks();
initInvalidFieldFocus();
initHistoryImages();
initArticleList();
initArticleForm();

Alpine.start();
