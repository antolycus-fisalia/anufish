import test from 'node:test';
import assert from 'node:assert/strict';

import { scanUpload } from '../../resources/js/scan.js';

function deferred() {
    let resolve;

    const promise = new Promise((resolver) => {
        resolve = resolver;
    });

    return { promise, resolve };
}

function createFile(name, type = 'image/jpeg') {
    return {
        name,
        type,
        size: 1024,
    };
}

function createInput(file) {
    const root = {
        dataset: {
            errorFormat: 'format-error',
            errorSize: 'size-error',
            errorImage: 'decode-error',
        },
    };

    return {
        files: file ? [file] : [],
        value: file?.name ?? '',
        closest() {
            return root;
        },
    };
}

function mockObjectUrls() {
    const originalCreateObjectURL = URL.createObjectURL;
    const originalRevokeObjectURL = URL.revokeObjectURL;

    const revoked = [];

    URL.createObjectURL = (file) => `blob:${file.name}`;

    URL.revokeObjectURL = (url) => {
        revoked.push(url);
    };

    return {
        revoked,

        restore() {
            URL.createObjectURL = originalCreateObjectURL;
            URL.revokeObjectURL = originalRevokeObjectURL;
        },
    };
}

test('stale successful decode cannot revive a file replaced by invalid input', async () => {
    const urls = mockObjectUrls();

    try {
        const component = scanUpload();
        const pendingA = deferred();

        component.canDecodeImage = () => pendingA.promise;

        const input = createInput(createFile('A.jpg'));

        const firstSelection = component.handleFile({
            target: input,
        });

        input.files = [
            createFile('invalid.txt', 'text/plain'),
        ];
        input.value = 'invalid.txt';

        await component.handleFile({
            target: input,
        });

        assert.equal(component.hasFile, false);
        assert.equal(component.selectedFileName, '');
        assert.equal(component.errorMessage, 'format-error');
        assert.equal(input.value, '');

        pendingA.resolve(true);
        await firstSelection;

        assert.equal(component.hasFile, false);
        assert.equal(component.selectedFileName, '');
        assert.equal(component.errorMessage, 'format-error');
        assert.equal(input.value, '');
        assert.ok(urls.revoked.includes('blob:A.jpg'));
    } finally {
        urls.restore();
    }
});

test('older successful decode cannot overwrite a newer valid image', async () => {
    const urls = mockObjectUrls();

    try {
        const component = scanUpload();

        const pendingA = deferred();
        const pendingB = deferred();

        component.canDecodeImage = (url) => {
            return url === 'blob:A.jpg'
                ? pendingA.promise
                : pendingB.promise;
        };

        const input = createInput(createFile('A.jpg'));

        const firstSelection = component.handleFile({
            target: input,
        });

        input.files = [createFile('B.jpg')];
        input.value = 'B.jpg';

        const secondSelection = component.handleFile({
            target: input,
        });

        // Pilihan terbaru selesai lebih dahulu.
        pendingB.resolve(true);
        await secondSelection;

        assert.equal(component.hasFile, true);
        assert.equal(component.selectedFileName, 'B.jpg');
        assert.equal(component.previewUrl, 'blob:B.jpg');

        // Decode lama kemudian selesai sukses.
        pendingA.resolve(true);
        await firstSelection;

        assert.equal(component.hasFile, true);
        assert.equal(component.selectedFileName, 'B.jpg');
        assert.equal(component.previewUrl, 'blob:B.jpg');
        assert.equal(component.errorMessage, '');
        assert.ok(urls.revoked.includes('blob:A.jpg'));
    } finally {
        urls.restore();
    }
});

test('older failed decode cannot clear a newer valid image', async () => {
    const urls = mockObjectUrls();

    try {
        const component = scanUpload();

        const pendingA = deferred();
        const pendingB = deferred();

        component.canDecodeImage = (url) => {
            return url === 'blob:A.jpg'
                ? pendingA.promise
                : pendingB.promise;
        };

        const input = createInput(createFile('A.jpg'));

        const firstSelection = component.handleFile({
            target: input,
        });

        input.files = [createFile('B.jpg')];
        input.value = 'B.jpg';

        const secondSelection = component.handleFile({
            target: input,
        });

        pendingB.resolve(true);
        await secondSelection;

        assert.equal(component.hasFile, true);
        assert.equal(component.selectedFileName, 'B.jpg');
        assert.equal(component.previewUrl, 'blob:B.jpg');

        // Hasil decode lama gagal setelah B sudah valid.
        pendingA.resolve(false);
        await firstSelection;

        assert.equal(component.hasFile, true);
        assert.equal(component.selectedFileName, 'B.jpg');
        assert.equal(component.previewUrl, 'blob:B.jpg');
        assert.equal(component.errorMessage, '');
        assert.ok(urls.revoked.includes('blob:A.jpg'));
    } finally {
        urls.restore();
    }
});

test('destroy invalidates decode that is still pending', async () => {
    const urls = mockObjectUrls();

    try {
        const component = scanUpload();
        const pending = deferred();

        component.canDecodeImage = () => pending.promise;

        const input = createInput(createFile('A.jpg'));

        const selection = component.handleFile({
            target: input,
        });

        component.destroy();

        pending.resolve(true);
        await selection;

        assert.equal(component.hasFile, false);
        assert.equal(component.selectedFileName, '');
        assert.equal(component.previewUrl, null);
        assert.ok(urls.revoked.includes('blob:A.jpg'));
    } finally {
        urls.restore();
    }
});
