import React from 'react';
import { InertiaLink } from '@inertiajs/inertia-react';

const Welcome = () => {
    return (
        <div>
            <h1>Welcome to the Application</h1>
            <p>Please login to access the site.</p>
            <InertiaLink href="/login">Login</InertiaLink>
        </div>
    );
};

export default Welcome;
