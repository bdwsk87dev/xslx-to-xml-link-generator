import React, {useState} from 'react';
import {Head, InertiaLink} from '@inertiajs/inertia-react';
import {format} from 'date-fns';
import {Inertia} from '@inertiajs/inertia'
import {Helmet} from 'react-helmet';

import EditForm from '../forms/EditForm'; // Import the EditForm component

const List = ({xmlFiles}) => {

    const [sortColumn, setSortColumn] = useState(null);
    const [sortDirection, setSortDirection] = useState('asc');
    const [searchString, ssetSarchString] = useState('');
    const [perPage, setperPage] = useState(10);
    const [page, setPage] = useState(1);
    const [editingProductId, setEditingProductId] = useState(null); // Track the editing product ID
    const [isEditFormOpen, setIsEditFormOpen] = useState(false);

    const openEditForm = () => {
        setIsEditFormOpen(true);
    };

    const closeEditForm = () => {
        setIsEditFormOpen(false);
    };


    // Handle the "Edit" button click
    const handleEdit = (id) => {
        openEditForm(id);
        setEditingProductId(id); // Set the editing product ID when the "Edit" button is clicked
    };

    const handleDelete = (id) => {
        if (window.confirm('Are you sure you want to delete this file?')) {
            Inertia.post(`/delete/${id}`, {}, {
                onSuccess: () => {
                    // Обновите список после успешного удаления
                    Inertia.reload();
                },
            });
        }
    }

    const sortBy = (column) => {
        let order = 'asc';
        if (sortColumn === column && sortDirection === 'asc') {
            order = 'desc';
        }
        Inertia.visit('/list', {
            method: 'get',
            data: {
                sort_by: column,
                order,
                per_page: perPage,
                search: searchString,
                page: page
            },
            preserveState: true
        });

        setSortColumn(column);
        setSortDirection(order);
    };
    const changePerPage = (e) => {
        const perPage = e.target.value;
        setperPage(perPage);
        Inertia.visit('/list', {
            method: 'get',
            data: {
                per_page: perPage,
                sort_by: sortColumn,
                order: sortDirection,
                search: searchString,
                page: 1
            },
            preserveState: true
        });
    }

    const changePage = (page) => {
        setPage(page);
        Inertia.visit('/list', {
            method: 'get',
            data: {
                sort_by: sortColumn,
                order: sortDirection,
                per_page: perPage,
                search: searchString,
                page: page
            },
            preserveState: true
        });
    }


    const search = (e) => {
        const searchString = e.target.value;
        if (e.key === 'Enter') {
            Inertia.visit('/list', {
                method: 'get',
                data: {
                    search: searchString,
                    sort_by: sortColumn,
                    order: sortDirection,
                    per_page: xmlFiles.per_page,
                    page: 1
                },
                preserveState: true
            });
            setSortColumn(null);
            setSortDirection('asc');
            ssetSarchString(searchString);
        }
    }

    return (
        <div className="p-5">
            <Helmet>
                <link
                    rel="stylesheet"
                    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
                    integrity="sha384-..."
                    crossorigin="anonymous"
                />

            </Helmet>

            <Head>
                <style>{`
                .custom-edit-button,
                .custom-delete-button,
                .link-button
                {
                    font-size:10px;
                    padding: 3px 8px;
                    margin-right: 5px;
                    min - height: 38px;
                }

                .modal-background {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }

           .modal {
  position: relative; /* Изменено на relative */
  z-index: 9999;
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
  text-align: center;
  display: block; /* Отображаем окно */
  width: 80%; /* Ширина окна */
  height:60%;
  max-width: 400px; /* Максимальная ширина окна */
}

input, select {
  padding: 8px;
  margin: 5px 0;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
  font-size: 14px;
}

select:hover {
cursor:pointer;
}

.pagination li:hover{
cursor:pointer;
}

                 `}</style>
            </Head>

            <h1>XML Files List</h1>
            <div style={{display: 'flex', justifyContent: 'space-between'}}>
                <input type="text" placeholder="Search..." onKeyDown={search}/>
                <select onChange={changePerPage} value={xmlFiles.per_page}>
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                    <option value="200">200 per page</option>
                    {/* Добавили опцию для 40 элементов на странице */}
                </select>
            </div>

            <br/>

            <table>
                <tr>
                    <td colSpan="6" style={{textAlign: 'center'}}>
                        {xmlFiles.links.length > 0 && (
                            <ul className="pagination">
                                {xmlFiles.links.map((link, key) => (
                                    <li key={key} className={`page-item ${link.active ? 'active' : ''}`}>
                                        {link.label !== "..." ? (
                                                <p onClick={() => changePage(link.url.match(/page=(\d+)/)?.[1])}
                                                   className="page-link">
                                                    {link.label.replace(/&laquo;/g, '').replace(/&raquo;/g, '')}
                                                </p>
                                            ) :
                                            <p className="page-link">
                                                ...
                                            </p>
                                        }
                                    </li>
                                ))}
                            </ul>
                        )}
                    </td>
                </tr>
            </table>


            <table style={{width: '100%', borderCollapse: 'collapse'}}>
                <thead>
                <tr>
                    <th onClick={() => sortBy('id')} style={{
                        cursor: 'pointer',
                        padding: '8px',
                        border: '1px solid #ddd',
                        backgroundColor: '#f2f2f2',
                        fontWeight: 'bold',
                        textAlign: 'left'
                    }}>ID
                    </th>
                    <th onClick={() => sortBy('shop_name')} style={{
                        cursor: 'pointer',
                        padding: '8px',
                        border: '1px solid #ddd',
                        backgroundColor: '#f2f2f2',
                        fontWeight: 'bold',
                        textAlign: 'left'
                    }}>Shop name
                    </th>
                    <th onClick={() => sortBy('shop_link')} style={{
                        cursor: 'pointer',
                        padding: '8px',
                        border: '1px solid #ddd',
                        backgroundColor: '#f2f2f2',
                        fontWeight: 'bold',
                        textAlign: 'left'
                    }}>Shop Link
                    </th>
                    <th onClick={() => sortBy('uploadDateTime')} style={{
                        cursor: 'pointer',
                        padding: '8px',
                        border: '1px solid #ddd',
                        backgroundColor: '#f2f2f2',
                        fontWeight: 'bold',
                        textAlign: 'left'
                    }}>Date upload
                    </th>
                    <th onClick={() => sortBy('')} style={{
                        cursor: 'pointer',
                        padding: '8px',
                        border: '1px solid #ddd',
                        backgroundColor: '#f2f2f2',
                        fontWeight: 'bold',
                        textAlign: 'left'
                    }}>Last update
                    </th>
                    <th style={{
                        padding: '8px',
                        border: '1px solid #ddd',
                        backgroundColor: '#f2f2f2',
                        fontWeight: 'bold',
                        textAlign: 'left'
                    }}>Link
                    </th>
                    <th style={{
                        padding: '8px',
                        border: '1px solid #ddd',
                        backgroundColor: '#f2f2f2',
                        fontWeight: 'bold',
                        textAlign: 'left'
                    }}>Edit
                    </th>
                </tr>
                </thead>
                <tbody>
                {xmlFiles.data.map((xmlFile) => (

                    <tr key={xmlFile.id}>
                        <td style={{padding: '8px', border: '1px solid #ddd'}}>{xmlFile.id}</td>
                        <td style={{padding: '8px', border: '1px solid #ddd'}}>{xmlFile.shop_name}</td>
                        <td style={{padding: '8px', border: '1px solid #ddd'}}><a href={xmlFile.shop_link}
                                                                                  target="_blank">{xmlFile.shop_link}</a>
                        </td>
                        <td style={{
                            padding: '8px',
                            border: '1px solid #ddd'
                        }}>{format(new Date(xmlFile.created_at), 'dd.MM.yyyy HH:mm:ss')}</td>

                        {/*<td style={{ padding: '8px', border: '1px solid #ddd' }}>{format(new Date(xmlFile.lastCheckDateTime), 'dd.MM.yyyy HH:mm:ss')}</td>*/}

                        <td style={{ padding: '8px', border: '1px solid #ddd' }}></td>


                        <td style={{padding: '8px', border: '1px solid #ddd'}}><a className="btn btn-success link-button" href={`/api/show/${xmlFile.id}`}
                                                                                  target="_blank">Link</a></td>


                        <td style={{padding: '8px', border: '1px solid #ddd'}}>
                            <button className="btn btn-primary edit-button custom-edit-button" onClick={() => handleEdit(xmlFile.id)}>
                                Edit
                            </button>
                            <button className="btn btn-danger delete-button custom-delete-button" onClick={() => handleDelete(xmlFile.id)}>
                                Delete
                            </button>
                        </td>
                    </tr>
                ))}
                </tbody>
                <tfoot>
                <tr>
                    <td colSpan="6" style={{textAlign: 'center'}}>
                        <br></br>
                        {xmlFiles.links.length > 0 && (
                            <ul className="pagination">
                                {xmlFiles.links.map((link, key) => (
                                    <li key={key} className={`page-item ${link.active ? 'active' : ''}`}>
                                        {link.label !== "..." ? (
                                                <p onClick={() => changePage(link.url.match(/page=(\d+)/)?.[1])}
                                                   className="page-link">
                                                    {link.label.replace(/&laquo;/g, '').replace(/&raquo;/g, '')}
                                                </p>
                                            ) :
                                            <p className="page-link">
                                                ...
                                            </p>
                                        }
                                    </li>
                                ))}
                            </ul>
                        )}
                    </td>
                </tr>
                </tfoot>
            </table>

            {isEditFormOpen && <EditForm productId={editingProductId} onClose={closeEditForm} />}

        </div>
    );
};

export default List;
