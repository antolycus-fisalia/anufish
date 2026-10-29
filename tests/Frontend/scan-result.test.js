import test from 'node:test';
import assert from 'node:assert/strict';

import { scanUpload } from '../../resources/js/scan.js';

function createComponent() {
    const component = scanUpload();
    component.navigations = [];
    component.openedTabs = [];
    globalThis.window = {open: (url, target) => {
        const tab = {url, target, closed: false, opener: {},
            close() { this.closed = true; },
            location: {replace: url => component.navigations.push(url)}};
        component.openedTabs.push(tab);
        return tab;
    }};

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

test('single AI candidate waits for explicit selection without fetching a reference', async () => {
    const component = createComponent();
    const requests = [];
    globalThis.fetch = async (url) => {
        requests.push(url);
        return successfulResponse([
            {
                scientific_name: 'Oreochromis niloticus',
                confidence: 0.92,
            },
        ]);
    };

    await component.processImage();

    assert.equal(component.resultStatus, 'ambiguous');
    assert.equal(component.selectedCandidateIndex, null);
    assert.deepEqual(requests, ['/scan/upload']);
    assert.deepEqual(component.navigations, []);
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
    assert.equal(component.resultStatus, 'ambiguous');
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
    assert.equal(component.resultStatus, 'ambiguous');


});

test('upload response without candidates shows fallback result error', async () => {
    const component = createComponent();

    globalThis.fetch = async () => ({
        ok: true,
        json: async () => ({
            success: true,
            data: {
                path: 'scans/test.jpg',
                disk: 'local',
                original_name: 'test.jpg',
                size: 1000,
                mime_type: 'image/jpeg',
            },
        }),
    });

    await component.processImage();

    assert.equal(component.resultStatus, 'failure');
    assert.equal(
        component.resultError,
        'Hasil identifikasi belum tersedia.'
    );
    assert.equal(component.processing, false);
});

test('validated reference is shown separately from unchanged AI confidence', async () => {
    const component = createComponent();
    const reference = {taxon_id: 10, scientific_name: 'Channa striata', image: null};
    globalThis.fetch = async (url) => url === '/scan/upload' ? successfulResponse([{
        scientific_name: 'Channa striata', confidence: null,
    }]) : {ok: true, json: async () => ({data: {match_status: 'matched', taxon_id: 10, reference, reference_options: []}})};
    await component.processImage();
    assert.deepEqual(component.navigations, []);
    await component.selectCandidate(0);
    assert.equal(component.resultStatus, 'success');
    assert.equal(component.referenceStatus, 'ready');
    assert.equal(component.reference.taxon_id, 10);
    assert.equal(component.candidates[0].confidence, null);
    assert.deepEqual(component.navigations, ['/fish/taxa/10']);
});

test('ambiguous taxa require explicit selection and reject unknown ids', async () => {
    const component = createComponent();
    globalThis.fetch = async (url) => url === '/scan/upload' ? successfulResponse([{
        scientific_name: 'Channa striata', confidence: 0.8,
    }]) : {ok: true, json: async () => ({data: {
        match_status: 'ambiguous', taxon_id: null, reference: null,
        reference_options: [{taxon_id: 10}, {taxon_id: 11}],
    }})};
    await component.processImage();
    await component.selectCandidate(0);
    assert.equal(component.referenceStatus, 'ambiguous');
    assert.equal(component.reference, null);
    component.selectReference(999);
    assert.equal(component.reference, null);
    assert.deepEqual(component.navigations, []);
    component.selectReference(11);
    assert.equal(component.reference.taxon_id, 11);
    assert.equal(component.resultStatus, 'success');
    assert.equal(component.candidates[0].taxon_id, null);
    assert.deepEqual(component.navigations, ['/fish/taxa/11']);
});

test('switching candidate clears stale reference and broken image state', async () => {
    const component = createComponent();
    const candidates = [
        {scientific_name: 'Channa striata', confidence: 0.9, match_status: 'matched', reference: {taxon_id: 10}},
        {scientific_name: 'Channa micropeltes', confidence: 0.1, match_status: 'unavailable', reference: null},
    ];
    globalThis.fetch = async (url) => url === '/scan/upload' ? successfulResponse(candidates)
        : {ok: true, json: async () => ({data: url.includes('micropeltes') ? candidates[1] : candidates[0]})};
    await component.processImage();
    assert.equal(component.selectedCandidateIndex, null);
    await component.selectCandidate(0);
    assert.equal(component.referenceStatus, 'ready');
    component.referenceImageFailed = true;
    await component.selectCandidate(1);
    assert.equal(component.resultStatus, 'partial');
    assert.equal(component.reference, null);
    assert.equal(component.referenceImageFailed, false);
    assert.equal(component.candidates[1].confidence, 0.1);
    component.resetResult();
    assert.deepEqual(component.referenceOptions, []);
});

test('selecting and retrying a candidate fetches its reference and displays provider errors', async () => {
    const component = createComponent();
    component.candidates = [{scientific_name: 'Isurus oxyrinchus', confidence: 0.3}];
    const requests = [];
    globalThis.fetch = async (url) => {
        requests.push(url);
        return {ok: true, json: async () => ({data: requests.length === 1
            ? {match_status: 'unavailable', reference_error: 'Waktu pengambilan habis.'}
            : {match_status: 'matched', reference: {taxon_id: 42}}})};
    };
    await component.selectCandidate(0);
    assert.equal(component.referenceError, 'Waktu pengambilan habis.');
    assert.equal(component.resultStatus, 'partial');
    assert.deepEqual(component.navigations, []);
    await component.selectCandidate(0);
    assert.equal(requests.length, 2);
    assert.match(requests[0], /scientific_name=Isurus\+oxyrinchus/);
    assert.equal(component.reference.taxon_id, 42);
    assert.equal(component.resultStatus, 'success');
    assert.equal(component.candidates[0].confidence, 0.3);
    assert.deepEqual(component.navigations, ['/fish/taxa/42']);
});

test('late reference responses cannot overwrite a newer candidate or a reset scan', async () => {
    const component = createComponent();
    component.candidates = [{scientific_name: 'First'}, {scientific_name: 'Second'}];
    const pending = [];
    globalThis.fetch = () => new Promise(resolve => pending.push(resolve));
    const first = component.selectCandidate(0);
    const second = component.selectCandidate(1);
    assert.equal(component.referenceStatus, 'loading');
    pending[1]({ok: true, json: async () => ({data: {match_status: 'matched', reference: {taxon_id: 2}}})});
    await second;
    pending[0]({ok: true, json: async () => ({data: {match_status: 'matched', reference: {taxon_id: 1}}})});
    await first;
    assert.equal(component.reference.taxon_id, 2);
    assert.deepEqual(component.navigations, ['/fish/taxa/2']);
    const third = component.selectCandidate(0);
    component.resetResult();
    pending[2]({ok: true, json: async () => ({data: {match_status: 'matched', reference: {taxon_id: 1}}})});
    await third;
    assert.equal(component.reference, null);
    assert.equal(component.resultStatus, 'idle');
    assert.deepEqual(component.navigations, ['/fish/taxa/2']);
});

test('opens a loading tab synchronously and ignores duplicate clicks while fetching', async () => {
    const component = createComponent();
    component.candidates = [{scientific_name: 'Channa striata'}];
    let finish;
    globalThis.fetch = () => new Promise(resolve => { finish = resolve; });
    const request = component.selectCandidate(0);
    assert.equal(component.openedTabs.length, 1);
    assert.equal(component.openedTabs[0].url, '/scan/detail-loading');
    assert.equal(component.openedTabs[0].target, '_blank');
    assert.equal(component.openedTabs[0].opener, null);
    assert.equal(component.referenceStatus, 'loading');
    await component.selectCandidate(0);
    assert.equal(component.openedTabs.length, 1);
    finish({ok: true, json: async () => ({data: {match_status: 'matched', reference: {taxon_id: 10}}})});
    await request;
    assert.equal(component.detailTabStatus, 'opened');
    assert.deepEqual(component.navigations, ['/fish/taxa/10']);
    component.resetResult();
    assert.equal(component.openedTabs[0].closed, false);
});

test('blocked popup preserves the reference for the manual new-tab link', async () => {
    const component = createComponent();
    component.candidates = [{scientific_name: 'Channa striata'}];
    globalThis.window.open = () => null;
    globalThis.fetch = async () => ({ok: true, json: async () => ({data: {match_status: 'matched', reference: {taxon_id: 10}}})});
    await component.selectCandidate(0);
    assert.equal(component.detailTabStatus, 'blocked');
    assert.equal(component.reference.taxon_id, 10);
    assert.equal(component.resultStatus, 'success');
    assert.deepEqual(component.navigations, []);
});

test('failed reference closes the waiting tab and keeps the failure in scan results', async () => {
    const component = createComponent();
    component.candidates = [{scientific_name: 'Channa striata'}];
    globalThis.fetch = async () => ({ok: true, json: async () => ({data: {match_status: 'unavailable', reference_error: 'Timeout'}})});
    await component.selectCandidate(0);
    assert.equal(component.openedTabs[0].closed, true);
    assert.equal(component.referenceError, 'Timeout');
    assert.deepEqual(component.navigations, []);
});
