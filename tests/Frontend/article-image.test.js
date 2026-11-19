import test from 'node:test';
import assert from 'node:assert/strict';
import { initArticleForm } from '../../resources/js/article-form.js';

test('article image selection previews WebP and keeps rejecting unsupported formats', async (context) => {
    class Element {
        attributes = new Map();
        listeners = new Map();
        hidden = true;
        addEventListener(name, listener) { this.listeners.set(name, listener); }
        setAttribute(name, value) { this.attributes.set(name, value); }
        removeAttribute(name) { this.attributes.delete(name); }
    }

    const selectors = [
        '[data-article-form-element]', '[data-article-image-input]',
        '[data-article-image-clear]', '[data-article-image-preview]',
        '[data-article-image-preview-image]', '[data-article-form-message]',
        '[data-article-form-message-text]', '[data-article-form-success-link]',
        '[data-article-submit]', '[data-submit-label]', '[data-submit-loading]',
        '[data-error-for="gambar"]',
    ];
    const elements = new Map(selectors.map((selector) => [selector, new Element()]));
    const input = elements.get('[data-article-image-input]');
    const form = elements.get('[data-article-form-element]');
    form.elements = { namedItem: () => input };
    const root = {
        querySelector: (selector) => elements.get(selector),
        dataset: {
            mode: 'create',
            imageMaxBytes: '5242880',
            errorImageFormat: 'format-error',
        },
    };
    const globals = {
        HTMLElement: Element,
        document: { querySelector: () => root },
        window: { addEventListener() {} },
        Image: class { async decode() {} },
    };
    for (const [name, value] of Object.entries(globals)) {
        const original = Object.getOwnPropertyDescriptor(globalThis, name);
        Object.defineProperty(globalThis, name, { configurable: true, value });
        context.after(() => {
            if (original) {
                Object.defineProperty(globalThis, name, original);
            } else {
                delete globalThis[name];
            }
        });
    }
    context.mock.method(URL, 'createObjectURL', (file) => `blob:${file.name}`);
    context.mock.method(URL, 'revokeObjectURL', () => {});
    initArticleForm();

    for (const name of ['fish.webp', 'fish.WEBP']) {
        input.files = [{ name, type: 'image/webp', size: 1024 }];
        await input.listeners.get('change')();
        assert.equal(elements.get('[data-article-image-preview]').hidden, false);
        assert.equal(elements.get('[data-article-image-preview-image]').src, `blob:${name}`);
        assert.equal(input.attributes.has('aria-invalid'), false);
    }
    input.files = [{ name: 'fish.gif', type: 'image/gif', size: 1024 }];
    await input.listeners.get('change')();
    assert.equal(elements.get('[data-article-image-preview]').hidden, true);
    assert.equal(elements.get('[data-error-for="gambar"]').textContent, 'format-error');
    assert.equal(input.attributes.get('aria-invalid'), 'true');
});
