import test from 'node:test';
import assert from 'node:assert/strict';
import { scanUpload } from '../../resources/js/scan.js';

function replaceGlobal(context, name, value) {
    const descriptor = Object.getOwnPropertyDescriptor(globalThis, name);
    Object.defineProperty(globalThis, name, { configurable: true, value });
    context.after(() => {
        if (descriptor) {
            Object.defineProperty(globalThis, name, descriptor);
        } else {
            delete globalThis[name];
        }
    });
}

function camera(context, getUserMedia, secure = true) {
    let stops = 0;
    const stream = { getTracks: () => [{ stop: () => stops++ }] };
    const messages = {
        cameraPermission: 'allow-camera',
        cameraReady: 'camera-ready',
        cameraDenied: 'permission-denied',
        cameraMissing: 'no-camera',
        cameraBusy: 'camera-busy',
        cameraFailed: 'capture-failed',
        cameraInsecure: 'use-https',
        cameraUnsupported: 'unsupported',
        cameraNative: 'native-camera',
    };
    replaceGlobal(context, 'navigator', {
        mediaDevices: { getUserMedia: getUserMedia ?? (async () => stream) },
        maxTouchPoints: 0,
    });
    replaceGlobal(context, 'window', { isSecureContext: secure });
    const component = scanUpload();
    component.$refs = {
        fileInput: { value: '', files: [], closest: () => ({ dataset: messages }) },
        cameraInput: { capture: 'environment', value: '', click() {} },
        cameraVideo: { srcObject: null, videoWidth: 1920, videoHeight: 1080, play: async () => {} },
    };
    return { component, stream, stopped: () => stops };
}

test('opening the camera asks for video only and shows permission and ready notices', async (context) => {
    const { component, stream, stopped } = camera(context);
    let resolve;
    let calls = 0;
    navigator.mediaDevices.getUserMedia = (constraints) => {
        calls++;
        assert.deepEqual(constraints, { video: { facingMode: { ideal: 'environment' } }, audio: false });
        return new Promise((done) => { resolve = done; });
    };
    const opening = component.openCamera();
    assert.equal(component.cameraNotice, 'allow-camera');
    assert.equal(component.cameraStatus, 'requesting');
    await component.openCamera();
    assert.equal(calls, 1);
    resolve(stream);
    await opening;
    assert.equal(component.$refs.cameraVideo.srcObject, stream);
    assert.equal(component.cameraStatus, 'ready');
    assert.equal(component.cameraNotice, 'camera-ready');
    component.stopCamera();
    assert.equal(stopped(), 1);
    assert.equal(component.$refs.cameraVideo.srcObject, null);
});

test('permission denial, missing cameras, and busy cameras show actionable errors', async (context) => {
    const { component } = camera(context);
    for (const [name, message] of [
        ['NotAllowedError', 'permission-denied'],
        ['NotFoundError', 'no-camera'],
        ['NotReadableError', 'camera-busy'],
    ]) {
        navigator.mediaDevices.getUserMedia = async () => { throw { name }; };
        await component.openCamera();
        assert.equal(component.cameraError, message);
        assert.equal(component.cameraStatus, 'idle');
    }
});

test('insecure mobile pages use native capture and desktops explain HTTPS', async (context) => {
    const { component } = camera(context, null, false);
    navigator.mediaDevices = undefined;
    let opened = 0;
    component.$refs.cameraInput.click = () => opened++;
    await component.openCamera();
    assert.equal(component.cameraError, 'use-https');
    assert.equal(opened, 0);
    navigator.maxTouchPoints = 5;
    await component.openCamera();
    assert.equal(opened, 1);
    assert.equal(component.cameraNotice, 'native-camera');
    assert.equal(component.cameraError, '');
});

test('closing a pending request releases a late stream instead of reopening the camera', async (context) => {
    const { component, stream, stopped } = camera(context);
    let resolve;
    navigator.mediaDevices.getUserMedia = () => new Promise((done) => { resolve = done; });
    const opening = component.openCamera();
    component.stopCamera();
    resolve(stream);
    await opening;
    assert.equal(stopped(), 1);
    assert.equal(component.cameraStatus, 'idle');
    assert.equal(component.$refs.cameraVideo.srcObject, null);
});

test('capturing stores a JPEG without assigning to the file input and stops the camera', async (context) => {
    const { component, stopped } = camera(context);
    const canvas = {
        getContext: () => ({ drawImage() {} }),
        toBlob: (done) => done(new Blob(['photo'], { type: 'image/jpeg' })),
    };
    replaceGlobal(context, 'document', {
        createElement: () => canvas,
        querySelector: () => ({ content: 'csrf-token' }),
    });
    context.mock.method(URL, 'createObjectURL', () => 'blob:photo');
    context.mock.method(URL, 'revokeObjectURL', () => {});
    component.canDecodeImage = async () => true;
    await component.openCamera();
    await component.capturePhoto();
    assert.equal(canvas.width, 1600);
    assert.equal(canvas.height, 900);
    assert.equal(component.selectedFile.type, 'image/jpeg');
    assert.equal(component.hasFile, true);
    assert.equal(component.previewUrl, 'blob:photo');
    assert.equal(component.$refs.fileInput.files.length, 0);
    assert.equal(stopped(), 1);
    assert.equal(component.cameraStatus, 'idle');

    replaceGlobal(context, 'fetch', async (url, request) => {
        assert.equal(url, '/scan/upload');
        const uploaded = request.body.get('fish_image');
        assert.equal(uploaded.type, 'image/jpeg');
        assert.equal(await uploaded.text(), 'photo');
        return { ok: true, json: async () => ({ success: true, data: { candidates: [] } }) };
    });
    await component.processImage();
    assert.equal(component.resultStatus, 'unknown');
});

test('destroying while capture is encoding discards the frame and releases the camera', async (context) => {
    const { component, stopped } = camera(context);
    let encode;
    replaceGlobal(context, 'document', { createElement: () => ({
        getContext: () => ({ drawImage() {} }),
        toBlob: (done) => { encode = done; },
    }) });
    await component.openCamera();
    const capture = component.capturePhoto();
    component.destroy();
    encode(new Blob(['photo'], { type: 'image/jpeg' }));
    await capture;
    assert.equal(component.hasFile, false);
    assert.equal(component.cameraStatus, 'idle');
    assert.equal(stopped(), 1);
});

test('canceling file selection preserves the existing photo and scan result', async () => {
    const component = scanUpload();
    component.hasFile = true;
    component.previewUrl = 'blob:existing';
    component.resultStatus = 'success';
    await component.handleFile({ target: { files: [] } });
    assert.equal(component.hasFile, true);
    assert.equal(component.previewUrl, 'blob:existing');
    assert.equal(component.resultStatus, 'success');
});

test('HTTP browsers without randomUUID still generate valid operation IDs', (context) => {
    const randomBytes = new Uint8Array(16).fill(123);
    replaceGlobal(context, 'crypto', { getRandomValues: () => randomBytes.slice() });
    assert.match(scanUpload().createOperationId(), /^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/);
});
