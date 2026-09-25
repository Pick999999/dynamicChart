// autoSaveInputs.js - Auto save and restore all input values to localStorage

(function() {
    'use strict';

    const CONFIG = {
        storagePrefix: 'autoSave_',
        debounceDelay: 300, // milliseconds
        excludeTypes: ['password', 'file'], // input types to exclude
        excludeClasses: ['no-autosave'], // elements with these classes won't be saved
    };

    // Debounce function to prevent excessive saves
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Get storage key for element
    function getStorageKey(element) {
        const id = element.id || element.name || element.dataset.autoSaveKey;

        if (!id) return null;
        return `${CONFIG.storagePrefix}${id}`;
    }

    // Check if element should be saved
    function shouldSaveElement(element) {
        // Skip if no id/name
        if (!element.id && !element.name && !element.dataset.autoSaveKey) {
            return false;
        }

        // Skip excluded types
        if (element.type && CONFIG.excludeTypes.includes(element.type)) {
            return false;
        }

        // Skip excluded classes
        if (CONFIG.excludeClasses.some(cls => element.classList.contains(cls))) {
            return false;
        }

        return true;
    }

    // Save element value to localStorage
    function saveValue(element) {
        if (!shouldSaveElement(element)) return;

        const key = getStorageKey(element);
        if (!key) return;

        let value;

        if (element.type === 'checkbox') {
            value = element.checked;
        } else if (element.type === 'radio') {
            if (element.checked) {
                value = element.value;
            } else {
                return; // Only save checked radio
            }
        } else if (element.tagName === 'SELECT' && element.multiple) {
            value = Array.from(element.selectedOptions).map(opt => opt.value);
        } else {
            value = element.value;
        }

        try {
            localStorage.setItem(key, JSON.stringify({
                value: value,
                timestamp: Date.now(),
                type: element.type || element.tagName.toLowerCase()
            }));
            console.log(`✓ Saved: ${key}`, value);
        } catch (e) {
            console.error('Failed to save to localStorage:', e);
        }
    }

    // Restore element value from localStorage
    function restoreValue(element) {
    if (!shouldSaveElement(element)) return;

    // ⭐ เพิ่มการตรวจสอบ nonsave attribute
    if (element.hasAttribute('nonsave') || element.getAttribute('nonsave') !== null) {
        console.log(`⊗ Skipped restore (nonsave): ${element.id || element.name}`);
        return;
    }

    const key = getStorageKey(element);
    if (!key) return;

    try {
        const stored = localStorage.getItem(key);
        if (!stored) return;

        const data = JSON.parse(stored);
        const value = data.value;

        if (element.type === 'checkbox') {
            element.checked = value === true || value === 'true';
        } else if (element.type === 'radio') {
            if (element.value === value) {
                element.checked = true;
            }
        } else if (element.tagName === 'SELECT' && element.multiple) {
            Array.from(element.options).forEach(opt => {
                opt.selected = Array.isArray(value) && value.includes(opt.value);
            });
        } else {
            element.value = value;
        }

        console.log(`✓ Restored: ${key}`, value);
    } catch (e) {
        console.error('Failed to restore from localStorage:', e);
    }
}

    // Debounced save function
    const debouncedSave = debounce(saveValue, CONFIG.debounceDelay);

    // Attach event listeners to element
    function attachListeners(element) {
        if (!shouldSaveElement(element)) return;

        if (element.type === 'checkbox' || element.type === 'radio') {
            element.addEventListener('change', () => saveValue(element));
        } else if (element.tagName === 'SELECT') {
            element.addEventListener('change', () => saveValue(element));
        } else {
            // For text inputs, use debounced save on input event
            element.addEventListener('input', () => debouncedSave(element));
            // Also save on blur to ensure value is saved
            element.addEventListener('blur', () => saveValue(element));
        }
    }

    // Initialize all inputs
    function initializeInputs() {
        const selectors = [
            'input:not([type="password"]):not([type="file"])',
            'textarea',
            'select'
        ];

        const elements = document.querySelectorAll(selectors.join(','));

        elements.forEach(element => {
            restoreValue(element);
            attachListeners(element);
        });

        console.log(`✓ AutoSave initialized: ${elements.length} elements tracked`);
    }

    // Watch for dynamically added elements
    function observeNewElements() {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        // Check if the node itself is an input
                        if (['INPUT', 'TEXTAREA', 'SELECT'].includes(node.tagName)) {
                            restoreValue(node);
                            attachListeners(node);
                        }

                        // Check for inputs within the node
                        const inputs = node.querySelectorAll('input, textarea, select');
                        inputs.forEach(input => {
                            restoreValue(input);
                            attachListeners(input);
                        });
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        console.log('✓ AutoSave: Observing DOM for new elements');
    }

    // Clear all saved data (utility function)
    window.autoSaveClear = function() {
        const keys = Object.keys(localStorage).filter(key =>
            key.startsWith(CONFIG.storagePrefix)
        );
        keys.forEach(key => localStorage.removeItem(key));
        console.log(`✓ Cleared ${keys.length} saved values`);
    };

    // Get all saved data (utility function)
    window.autoSaveGetAll = function() {
        const data = {};
        Object.keys(localStorage).forEach(key => {
            if (key.startsWith(CONFIG.storagePrefix)) {
                try {
                    data[key.replace(CONFIG.storagePrefix, '')] = JSON.parse(localStorage.getItem(key));
                } catch (e) {
                    console.error('Error parsing:', key, e);
                }
            }
        });
        return data;
    };

    // Export configuration for customization
    window.autoSaveConfig = CONFIG;

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initializeInputs();
            observeNewElements();
        });
    } else {
        initializeInputs();
        observeNewElements();
    }

    console.log('✓ AutoSave script loaded');
})();