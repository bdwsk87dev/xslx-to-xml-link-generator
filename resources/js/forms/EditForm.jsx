import React from 'react';

const EditForm = ({ productId, onClose }) => {
    return (
        <div className="modal-background">
            <div className="modal">
                <h2>Edit Product</h2>
                <p>Edit Product ID: {productId}</p>
                {/* Add your edit form fields here */}
                <button onClick={onClose}>Close</button>
            </div>
        </div>
    );
};

export default EditForm;
