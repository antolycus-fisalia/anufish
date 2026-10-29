import test from 'node:test';
import assert from 'node:assert/strict';

import { initInvalidFieldFocus } from '../../resources/js/ui.js';

test('first invalid field receives focus', () => {
    let focused = false;

    const invalidField = {
        focus() {
            focused = true;
        },
    };

    globalThis.document = {
        querySelector(selector) {
            assert.equal(
                selector,
                '[aria-invalid="true"]:not([disabled])'
            );

            return invalidField;
        },
    };

    initInvalidFieldFocus();

    assert.equal(focused, true);
});

test('page without invalid field does not throw', () => {
    globalThis.document = {
        querySelector() {
            return null;
        },
    };

    assert.doesNotThrow(() => {
        initInvalidFieldFocus();
    });
});
