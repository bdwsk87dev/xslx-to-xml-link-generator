import React from 'react';

const Show = ({ xmlFile, content }) => {
    // Создаем DOM парсер для XML
    const parser = new DOMParser();
    const xmlDoc = parser.parseFromString(content, 'application/xml');

    // Получаем корневой элемент XML
    const rootElement = xmlDoc.documentElement;

    return (
        <div>
            <pre>{rootElement.outerHTML}</pre>
        </div>
    );
};

export default Show;
