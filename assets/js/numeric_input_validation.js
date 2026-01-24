document.addEventListener('DOMContentLoaded', () => {
    const numericInputs = document.querySelectorAll('.js-numeric-input');

    const restrictToNumeric = (inputElement) => {
        if (inputElement) {
            inputElement.addEventListener('input', (event) => {
                let value = event.target.value;

                // Replace any character that is not a digit or a dot
                value = value.replace(/[^0-9.]/g, '');

                // Ensure there is only one dot
                const dotIndex = value.indexOf('.');
                if (dotIndex !== -1) {
                    const secondDotIndex = value.indexOf('.', dotIndex + 1);
                    if (secondDotIndex !== -1) {
                        value = value.substring(0, secondDotIndex);
                    }
                }

                event.target.value = value;
            });
        }
    };

    numericInputs.forEach(restrictToNumeric);

    const formatThousandsInputs = document.querySelectorAll('.js-format-thousands');

    const formatThousands = (inputElement) => {
        if (!inputElement) return;

        inputElement.addEventListener('input', (event) => {
            let value = event.target.value;

            // 1. Sanitize input: allow only digits and one decimal separator (dot or comma)
            let rawValue = value.replace(/[^\d,.]/g, '').replace(',', '.');
            const parts = rawValue.split('.');
            if (parts.length > 2) {
                rawValue = parts[0] + '.' + parts.slice(1).join('');
            }
            
            if (rawValue === '') {
                event.target.value = '';
                return;
            }

            const [integerPart, decimalPart] = rawValue.split('.');

            // 2. Format the integer part with non-breaking spaces
            const formattedIntegerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, '\u00A0');

            // 3. Reconstruct the value with a comma for the decimal part
            let formattedValue = formattedIntegerPart;
            if (decimalPart !== undefined) {
                formattedValue += ',' + decimalPart.substring(0, 2);
            }
            
            // Avoid infinite loop by checking if the value has changed
            if (event.target.value !== formattedValue) {
                event.target.value = formattedValue;
            }
        });
    };

    formatThousandsInputs.forEach(formatThousands);
});
