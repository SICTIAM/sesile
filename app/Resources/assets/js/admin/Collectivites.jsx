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
        const { t, _addNotification } = this.context
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
        const filteredCollectivites = this.state.collectivites.filter(collectivite=> regex.test(collectivite.nom))
        this.setState({filteredCollectivites})
    }
    render() {
        const { t } = this.context
        const listFilteredCollectivites =
            this.state.filteredCollectivites.map((collectivite, key) =>
                <RowCollectivite key={key} collectivite={collectivite} />)
        return (
            <AdminPage
                title={t('admin.collectivite.title')}
                subtitle={t('admin.subtitle')}>
                <AdminContainer>
                    <Cell className="medium-6">
                        <GridX className="grid-padding-x align-center-middle">
                            <Input
                                className="cell medium-auto"
                                labelText={t('admin.collectivite.which')}
                                value={this.state.collectiviteName}
                                onChange={this.handleSearchByCollectiviteName}
                                placeholder={t('common.search_by_name')}
                                type="text"/>
                        </GridX>
                    </Cell>
                    <AdminList
                        title={t('admin.collectivite.collectivites_list')}
                        listLength={listFilteredCollectivites.length}
                        headTitles={[t('common.label.name'), t('admin.collectivite.state'), t('common.label.actions')]}
                        headGrid={['medium-auto', 'medium-auto', 'medium-2']}
                        emptyListMessage={t('admin.collectivite.no_results')}>
                        {listFilteredCollectivites}
                    </AdminList>
                </AdminContainer>
            </AdminPage>
        )
    }
}

Collectivites.propTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Collectivites)

const RowCollectivite = ({collectivite}, {t}) =>
    <AdminListRow>
        <Cell className="medium-auto">
            <DisplayLongText text={collectivite.nom} maxSize={100}/>
        </Cell>
        <Cell className="medium-auto">
            {(collectivite.active) ? t('common.label.enabled') : t('common.label.disabled')}
        </Cell>
        <Cell className="medium-2">
            <GridX>
                <Cell className="medium-auto">
                    <Link
                        to={`collectivite/${collectivite.id}/utilisateurs-ozwillo`}
                        className="fa fa-users icon-action"
                        title={t('common.button.list_ozwillo')}/>
                </Cell>
                <Cell className="medium-auto">
                    <Link
                        to={`collectivite/${collectivite.id}`}
                        className="fa fa-pencil icon-action"
                        title={t('common.button.edit')}/>
                </Cell>
            </GridX>
        </Cell>
    </AdminListRow>

RowCollectivite.propTypes = {
    collectivite: object.isRequired
}

RowCollectivite.contextTypes = {
    t: func
}