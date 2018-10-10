import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { Link } from 'react-router-dom'
import { translate } from 'react-i18next'

import { AdminList, AdminPage, AdminContainer, AdminListRow } from "../_components/AdminUI"
import { Input } from '../_components/Form'
import { basicNotification } from '../_components/Notifications'
import { Cell, GridX } from '../_components/UI'

import { escapedValue } from '../_utils/Search'
import { handleErrors, DisplayLongText } from "../_utils/Utils"
import History from "../_utils/History";
import ButtonDropdown from "../_components/ButtonDropdown";
import ButtonConfirmDelete from "../_components/ButtonConfirmDelete";

class Collectivites extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    state = {
        collectivites: [],
        filteredCollectivites: [],
        collectiviteName: ''
    }

    componentDidMount() {
        this.fetchCollectivites()
    }

    fetchCollectivites() {
        const {t, _addNotification} = this.context
        fetch(Routing.generate('sesile_main_collectiviteapi_getall'), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => this.setState({collectivites: json, filteredCollectivites: json}))
            .catch(() =>
                _addNotification(basicNotification('error', t('admin.collectivite.error.fetch_list'))))
    }

    handleSearchByCollectiviteName = (key, collectiviteName) => {
        this.setState({collectiviteName})
        const regex = escapedValue(collectiviteName, this.state.filteredCollectivites, this.state.collectivites)
        const filteredCollectivites = this.state.collectivites.filter(collectivite => regex.test(collectivite.nom))
        this.setState({filteredCollectivites})
    }
    onClickButtonUserList = (e, collectivite) => {
        e.preventDefault()
        e.stopPropagation()
        History.push(`/admin/collectivite/${collectivite.id}/utilisateurs-ozwillo`)
    }

    render() {
        const {t} = this.context
        const listFilteredCollectivites =
            this.state.filteredCollectivites.map((collectivite, key) =>
                <RowCollectivite key={key} collectivite={collectivite} onClick={this.onClickButtonUserList}/>)
        return (
            <AdminPage
                title={t('admin.collectivite.name_plural')}>
                <AdminContainer>
                    <div className="grid-x grid-padding-x panel align-center-middle"
                         style={{width: "74em", marginTop: "1em"}}>
                        <div className="cell medium-12 grid-x panel align-center-middle"
                             style={{display: "flex", marginBottom: "0em", marginTop: "10px",padding:"10px", width: "50%"}}>
                                <input
                                    className="cell medium-auto"
                                    style={{margin:"0"}}
                                    value={this.state.collectiviteName}
                                    onChange={this.handleSearchByCollectiviteName}
                                    placeholder={t('common.search_by_name')}
                                    type="text"/>
                        </div>
                        <table style={{margin:"10px", borderRadius:"6px"}}>
                            <thead>
                            <tr style={{backgroundColor:"#CC0066", color:"white"}}>
                                <td width="300px" className="text-bold">{ t('common.label.name') }</td>
                                <td width="300px" className="text-bold">{ t('admin.collectivite.domain') }</td>
                                <td width="20px" className="text-bold">{ t('admin.collectivite.state') }</td>
                                <td width="20px" className="text-bold">{ t('common.label.actions') }</td>
                            </tr>
                            </thead>
                            <tbody>
                            {listFilteredCollectivites.length > 0 ?
                                listFilteredCollectivites :
                                <tr>
                                    <td>
                                        <span style={{textAlign:"center"}}>{this.props.message}</span>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>}
                            </tbody>
                        </table>
                    </div>
                </AdminContainer>
            </AdminPage>
        )
    }
}

Collectivites.propTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Collectivites)

const RowCollectivite = ({collectivite, onClick}, {t}) =>
    <tr onClick={() => History.push(`/admin/collectivite/${collectivite.id}`)} style={{cursor:"Pointer"}}>
        <td>
            {collectivite.nom}
        </td>
        <td>
            {collectivite.domain}
        </td>
        <td>
            {(collectivite.active) ? <div className="text-success">{t('common.label.enabled')}</div> : <div className="text-alert">{t('common.label.disabled')}</div>}
        </td>
        <td>
            <img onClick={(e) => onClick(e, collectivite)} title={t('common.button.list_ozwillo')} src="https://www.ozwillo.com/static/img/favicons/favicon-96x96.png" style={{width:"24px"}}/>
        </td>
    </tr>

RowCollectivite.propTypes = {
    collectivite: object.isRequired
}

RowCollectivite.contextTypes = {
    t: func
}