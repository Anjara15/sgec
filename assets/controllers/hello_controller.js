import { Controller } from '@hotwired/stimulus';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="" attribute will cause
 * this controller to be executed. The name "" comes from the filename:
 * _controller.js -> ""
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    connect() {
        this.element.textContent = ' Stimulus! Edit me in assets/controllers/_controller.js';
    }
}
