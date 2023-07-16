import React from 'react';
import { render } from 'react-dom';
import { App as InertiaApp } from '@inertiajs/inertia-react';
import { InertiaProgress } from '@inertiajs/progress';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

InertiaProgress.init();

const app = document.getElementById('app');

render(
    <InertiaApp
        initialPage={JSON.parse(app.dataset.page)}
        resolveComponent={async (name) => {
            const module = await import(`./pages/${name}.jsx`);
            return module.default;
        }}
        onBeforePageLoad={() => {
            // Дополнительный код, который выполнится перед загрузкой новой страницы
        }}
    />,
    app
);
