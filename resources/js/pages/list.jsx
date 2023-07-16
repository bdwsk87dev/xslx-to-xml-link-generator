import React, { useState } from 'react';
import {InertiaLink} from '@inertiajs/inertia-react';
import { format } from 'date-fns';
import { Inertia } from '@inertiajs/inertia'
import { Helmet } from 'react-helmet';

const List = ({xmlFiles}) => {

    const [sortColumn, setSortColumn] = useState(null);
    const [sortDirection, setSortDirection] = useState('asc');
    const [searchString, ssetSarchString] = useState('');
    const [perPage, setperPage] = useState(10);
    const [page, setPage] = useState(1);

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
            <div style={{ display: 'flex', justifyContent: 'space-between' }}>
                <input type="text" placeholder="Search..." onKeyDown={search} />
                <select onChange={changePerPage} value={xmlFiles.per_page}>
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                    <option value="100">100 per page</option>
                    <option value="200">200 per page</option> {/* Добавили опцию для 40 элементов на странице */}
                </select>
            </div>
            <h1>XML Files List</h1>
            <table><tr>
                <td colSpan="6" style={{ textAlign: 'center' }}>
                    {xmlFiles.links.length > 0 && (
                        <ul className="pagination">
                            {xmlFiles.links.map((link, key) => (
                                <li key={key} className={`page-item ${link.active ? 'active' : ''}`}>
                                    {link.label !== "..." ? (
                                        <p onClick={() => changePage(link.url.match(/page=(\d+)/)?.[1])} className="page-link">
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
            </tr></table>


            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                <thead>
                <tr>
                    <th onClick={() => sortBy('id')} style={{ cursor: 'pointer', padding: '8px', border: '1px solid #ddd', backgroundColor: '#f2f2f2', fontWeight: 'bold', textAlign: 'left' }}>ID</th>
                    <th onClick={() => sortBy('shop_name')} style={{ cursor: 'pointer', padding: '8px', border: '1px solid #ddd', backgroundColor: '#f2f2f2', fontWeight: 'bold', textAlign: 'left' }}>Shop name</th>
                    <th onClick={() => sortBy('shop_link')} style={{ cursor: 'pointer', padding: '8px', border: '1px solid #ddd', backgroundColor: '#f2f2f2', fontWeight: 'bold', textAlign: 'left' }}>Shop Link</th>
                    <th onClick={() => sortBy('uploadDateTime')} style={{ cursor: 'pointer', padding: '8px', border: '1px solid #ddd', backgroundColor: '#f2f2f2', fontWeight: 'bold', textAlign: 'left' }}>Date upload</th>
                    <th onClick={() => sortBy('lastCheckDateTime')} style={{ cursor: 'pointer', padding: '8px', border: '1px solid #ddd', backgroundColor: '#f2f2f2', fontWeight: 'bold', textAlign: 'left' }}>Last check</th>
                    <th style={{ padding: '8px', border: '1px solid #ddd', backgroundColor: '#f2f2f2', fontWeight: 'bold', textAlign: 'left' }}>Link</th>
                </tr>
                </thead>
                <tbody>
                {xmlFiles.data.map((xmlFile) => (

                    <tr key={xmlFile.id}>
                        <td style={{ padding: '8px', border: '1px solid #ddd' }}>{xmlFile.id}</td>
                        <td style={{ padding: '8px', border: '1px solid #ddd' }}>{xmlFile.shop_name}</td>
                        <td style={{ padding: '8px', border: '1px solid #ddd' }}><a href={xmlFile.shop_link} target="_blank">{xmlFile.shop_link}</a></td>
                        <td style={{ padding: '8px', border: '1px solid #ddd' }}>{format(new Date(xmlFile.created_at), 'dd.MM.yyyy HH:mm:ss')}</td>
                        <td style={{ padding: '8px', border: '1px solid #ddd' }}>{format(new Date(xmlFile.lastCheckDateTime), 'dd.MM.yyyy HH:mm:ss')}</td>
                        <td style={{ padding: '8px', border: '1px solid #ddd' }}><a href={`/api/show/${xmlFile.id}`} target="_blank">Link</a></td>

                    </tr>
                ))}
                </tbody>
                <tfoot>
                <tr>
                    <td colSpan="6" style={{ textAlign: 'center' }}>
                        <br></br>
                        {xmlFiles.links.length > 0 && (
                            <ul className="pagination">
                                {xmlFiles.links.map((link, key) => (
                                    <li key={key} className={`page-item ${link.active ? 'active' : ''}`}>
                                        {link.label !== "..." ? (
                                                <p onClick={() => changePage(link.url.match(/page=(\d+)/)?.[1])} className="page-link">
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
        </div>
    );
};

export default List;
