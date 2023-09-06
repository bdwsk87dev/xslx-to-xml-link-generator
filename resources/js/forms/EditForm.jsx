import React, { useState } from 'react';
import axios from 'axios';

const EditForm = ({ productId, onClose }) => {
    const [formData, setFormData] = useState({
        productId: productId,
        shop_name: '',
        shop_link: '',
        uploadNewXLS: false,
        deleteProducts: false,
        allowNewProducts: false,
        filename: '',
    });

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        setFormData((prevFormData) => ({
            ...prevFormData,
            filename: file,
        }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            const formDataToSend = new FormData();
            formDataToSend.append('productId', formData.productId);
            formDataToSend.append('shop_name', formData.shop_name);
            formDataToSend.append('shop_link', formData.shop_link);
            formDataToSend.append('uploadNewXLS', formData.uploadNewXLS);
            formDataToSend.append('deleteProducts', formData.deleteProducts);
            formDataToSend.append('allowNewProducts', formData.allowNewProducts);
            formDataToSend.append('filename', formData.filename);

            const response = await axios.post('/api/edit', formDataToSend);
            console.log('Ответ от сервера:', response.data);
            onClose();
        } catch (error) {
            console.error('Произошла ошибка при отправке данных:', error);
        }
    };

    const handleChange = (e) => {
        const { name, type } = e.target;
        const value = type === 'checkbox' ? e.target.checked : e.target.value;

        if (name === 'filename') {
            setFormData((prevFormData) => ({
                ...prevFormData,
                [name]: e.target.files[0],
            }));
        } else {
            setFormData((prevFormData) => ({
                ...prevFormData,
                [name]: value,
            }));
        }
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
                                    onChange={(e) => handleFileChange(e)}
                                />
                            </label>

                            <select name="updateType" value={formData.updateType} onChange={handleChange}>
                                <option value="updatePrices">Оновити ціни з нового файлу</option>
                            </select>

                            <label>
                                Видалити товари яких немає?
                                <input
                                    type="checkbox"
                                    name="deleteProducts"
                                    checked={formData.deleteProducts}
                                    onChange={handleChange}
                                />
                            </label>

                            <label>
                                Додавати нові товари з нового файлу?
                                <input
                                    type="checkbox"
                                    name="allowNewProducts"
                                    checked={formData.allowNewProducts}
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
