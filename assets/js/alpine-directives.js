// Tooltip directive para Alpine.js
document.addEventListener('alpine:init', () => {
    Alpine.directive('tooltip', (el, { expression }) => {
        let tooltip = document.createElement('div');
        tooltip.className = 'absolute z-50 px-2 py-1 text-xs font-medium text-white bg-gray-900 rounded-lg shadow-sm opacity-0 invisible transition-all duration-200 transform -translate-y-2';
        tooltip.textContent = expression;
        el.appendChild(tooltip);

        el.classList.add('relative');

        el.addEventListener('mouseenter', () => {
            tooltip.classList.remove('opacity-0', 'invisible', '-translate-y-2');
            tooltip.classList.add('opacity-100', 'visible', 'translate-y-0');
        });

        el.addEventListener('mouseleave', () => {
            tooltip.classList.remove('opacity-100', 'visible', 'translate-y-0');
            tooltip.classList.add('opacity-0', 'invisible', '-translate-y-2');
        });
    });
});
