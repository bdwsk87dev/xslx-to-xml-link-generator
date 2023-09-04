import React, { useState } from 'react';

const EditForm = ({ productId, onClose }) => {
    const [formData, setFormData] = useState({
        shop_name: '',
        shop_link: '',
        uploadNewXLS: false,
        deleteProducts: false,
        filename: '',
    });

    const handleChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData({
            [name]: type === 'checkbox' ? checked : value,
        });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        onClose();
    };

    return (
        <div className="modal-background">
            <div className="modal">
                <h2>Редагування файлу</h2>
                <p>ID: {productId}</p>
                <form onSubmit={handleSubmit}>
                    <div>
                        <label>
                            Назва магазину
                            <input
                                type="text"
                                name="shop_name"
                                value={formData.shop_name}
                                onChange={handleChange}
                            />
                        </label>
                    </div>
                    <div>
                        <label>
                            Лінк на магазин :
                            <input
                                type="text"
                                name="shop_link"
                                value={formData.shop_link}
                                onChange={handleChange}
                            />
                        </label>
                    </div>
                    <div>
                        <label>
                            Завантажити новий xlsx файл :
                            <input
                                type="checkbox"
                                name="uploadNewXLS"
                                checked={formData.uploadNewXLS}
                                onChange={handleChange}
                            />
                        </label>
                    </div>
                    {formData.uploadNewXLS && (

                        <div>
                            <label>
                                Filename:
                                <input
                                    type="file"
                                    name="filename"
                                    onChange={handleChange}
                                />
                            </label>

                            <select name="updateType" value={formData.updateType} onChange={handleChange}>
                                <option value="updatePrices">Оновити ціни з нового файлу</option>
                            </select>

                            <label>
                                Видалити товари яких немає ?
                                <input
                                    type="checkbox"
                                    name="deleteProducts"
                                    checked={formData.deleteProducts}
                                    onChange={handleChange}
                                />
                            </label>

                        </div>
                )}
                    <button type="submit">Save</button>
                    <button onClick={onClose}>Close</button>
                </form>
            </div>
        </div>
    );
};

export default EditForm;
