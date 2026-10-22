import test from 'node:test';
import assert from 'node:assert/strict';

import { scanUpload } from '../../resources/js/scan.js';

function createComponent() {
    const component = scanUpload();

    const root = {
        dataset: {
            errorRequest: 'Identifikasi gagal.',
            errorResult: 'Hasil identifikasi belum tersedia.',
        },
    };

    component.hasFile = true;
    component.$refs = {
        fileInput: {
            files: [
                new Blob(['fish-image'], {
                    type: 'image/jpeg',
                }),
            ],
            closest: () => root,
        },
    };

    globalThis.document = {
        querySelector: () => ({
            content: 'test-csrf-token',
        }),
    };

    return component;
}

function successfulResponse(candidates) {
    return {
        ok: true,
        json: async () => ({
            success: true,
            data: {
                candidates,
            },
        }),
    };
}

test('single Gemini candidate becomes partial while reference is unavailable', async () => {
    const component = createComponent();

    globalThis.fetch = async () =>
        successfulResponse([
            {
                scientific_name: 'Oreochromis niloticus',
                confidence: 0.92,
            },
        ]);

    await component.processImage();

    assert.equal(component.resultStatus, 'partial');
    assert.equal(component.selectedCandidateIndex, 0);
    assert.equal(component.candidates.length, 1);
    assert.equal(component.processing, false);
});

test('multiple candidates require explicit user selection', async () => {
    const component = createComponent();

    globalThis.fetch = async () =>
        successfulResponse([
            {
                scientific_name: 'Oreochromis niloticus',
                confidence: 0.88,
            },
            {
                scientific_name: 'Oreochromis mossambicus',
                confidence: 0.71,
            },
        ]);

    await component.processImage();

    assert.equal(component.resultStatus, 'ambiguous');
    assert.equal(component.selectedCandidateIndex, null);

    component.selectCandidate(1);

    assert.equal(component.selectedCandidateIndex, 1);
});

test('null confidence is displayed as unavailable', () => {
    const component = createComponent();

    assert.equal(component.formatConfidence(null), '');
    assert.equal(component.formatConfidence(undefined), '');
    assert.equal(component.formatConfidence(0.85), '85%');
});

test('empty Gemini candidates becomes unknown', async () => {
    const component = createComponent();

    globalThis.fetch = async () =>
        successfulResponse([]);

    await component.processImage();

    assert.equal(component.resultStatus, 'unknown');
    assert.deepEqual(component.candidates, []);
});

test('failed request becomes failure state', async () => {
    const component = createComponent();

    globalThis.fetch = async () => ({
        ok: false,
        json: async () => ({
            success: false,
            message: 'Gemini tidak dapat memproses gambar.',
        }),
    });

    await component.processImage();

    assert.equal(component.resultStatus, 'failure');
    assert.equal(
        component.resultError,
        'Gemini tidak dapat memproses gambar.'
    );
    assert.equal(component.processing, false);
});

test('identification can be retried after failure', async () => {
    const component = createComponent();

    let attempt = 0;

    globalThis.fetch = async () => {
        attempt += 1;

        if (attempt === 1) {
            return {
                ok: false,
                json: async () => ({
                    success: false,
                    message: 'Identifikasi gagal.',
                }),
            };
        }

        return successfulResponse([
            {
                scientific_name: 'Oreochromis niloticus',
                confidence: 0.9,
            },
        ]);
    };

    await component.processImage();

    assert.equal(component.resultStatus, 'failure');

    await component.processImage();

    assert.equal(attempt, 2);
    assert.equal(component.resultStatus, 'partial');
    assert.equal(component.processing, false);
});

test('double click does not start duplicate identification request', async () => {
    const component = createComponent();

    let requestCount = 0;
    let resolveRequest;

    globalThis.fetch = () => {
        requestCount += 1;

        return new Promise((resolve) => {
            resolveRequest = () => {
                resolve(
                    successfulResponse([
                        {
                            scientific_name: 'Oreochromis niloticus',
                            confidence: 0.93,
                        },
                    ])
                );
            };
        });
    };

    const firstRequest = component.processImage();
    const secondRequest = component.processImage();

    assert.equal(requestCount, 1);
    assert.equal(component.processing, true);

    resolveRequest();

    await Promise.all([
        firstRequest,
        secondRequest,
    ]);

    assert.equal(requestCount, 1);
    assert.equal(component.processing, false);
    assert.equal(component.resultStatus, 'partial');
});
