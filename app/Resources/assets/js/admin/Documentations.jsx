import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import Moment from 'moment'
import { Link, Redirect } from 'react-router-dom'

import { AdminList, AdminPage, AdminContainer, AdminListRow } from "../_components/AdminUI"
import ButtonConfirmDelete from '../_components/ButtonConfirmDelete'
import { Input } from '../_components/Form'
import { Cell, GridX } from '../_components/UI'
import { basicNotification } from "../_components/Notifications"
import { handleErrors } from '../_utils/Utils'
import History from '../_utils/History'
import { escapedValue } from '../_utils/Search'


class Documentations extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }
    state = {
        searchPatchByDescription: '',
        searchHelpByDescription: '',
        helps: [],
        patchs: [],
        filteredPatchs: [],
        filteredHelps: []
    }
    componentDidMount() {
        this.fetchHelps()
        this.fetchPatchs()
    }
    fetchHelps = () => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_main_documentationapi_getallaide'), { credentials: 'same-origin'})
        .then(handleErrors)
            .then(response => response.json())
            .then(helps => {
                this.setState({helps, filteredHelps: helps})
            })
        .catch(error => _addNotification(basicNotification(
           'error',
           t('admin.error.not_extractable_list', {name: t('common.help_board.title_helps'), errorCode: error.status}),
           error.statusText)))
    }
    fetchPatchs = () => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_main_documentationapi_getallpatch'), { credentials: 'same-origin'})
        .then(handleErrors)
            .then(response => response.json())
            .then(patchs => {
                this.setState({patchs, filteredPatchs: patchs})
            })
        .catch(error => _addNotification(basicNotification(
           'error',
           t('admin.error.not_extractable_list', {name: t('common.help_board.title_patchs'), errorCode: error.status}),
           error.statusText)))
    }
    deleteHelp = (id) => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_main_documentationapi_removeaide', {id}), {
            method: 'DELETE',
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => response.json())
        .then(helps => {
            _addNotification(
                basicNotification(
                    'success',
                    t('admin.documentations.succes_delete')))
            this.setState({helps, filteredHelps: helps})
        })
        .catch(error => _addNotification(
            basicNotification(
                'error',
                t('admin.documentations.error.delete'),
                error.statusText)))
    }
    deletePatch = (id) => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_main_documentationapi_removepatch', {id}), {
            method: 'DELETE',
            credentials: 'same-origin'})
        .then(handleErrors)
        .then(response => response.json())
        .then(patchs => {
            _addNotification(basicNotification(
                'success',
                t('admin.documentations.succes_delete')))
            this.setState({patchs, filteredPatchs: patchs})
        })
        .catch(error => _addNotification(
            basicNotification(
                'error',
                t('admin.documentations.error.delete'),
                error.statusText)))
    }
    searchPatchByDescription = (key, searchPatchByDescription) => {
        this.setState({searchPatchByDescription})
        const regex = escapedValue(searchPatchByDescription, this.state.filteredPatchs, this.state.patchs)
        const filteredPatchs = this.state.patchs.filter(patch => regex.test(patch.description))
        this.setState({filteredPatchs})
    }
    searchHelpByDescription = (key, searchHelpByDescription) => {
        this.setState({searchHelpByDescription})
        const regex = escapedValue(searchHelpByDescription, this.state.filteredHelps, this.state.helps)
        const filteredHelps = this.state.helps.filter(help => regex.test(help.description))
        this.setState({filteredHelps})
    }
    render() {
        const { t } = this.context
        const { filteredHelps, filteredPatchs } = this.state
        const listDocumentEvo = filteredPatchs.map((patch,key) => <RowDocumentEvo key={key} patch={patch} deletePatch={this.deletePatch} />)
        const listDocumentHelp = filteredHelps.map((help,key) => <RowDocumentHelp key={key} help={help} deleteHelp={this.deleteHelp} />)
        return (
            <AdminPage
                title={t('admin.documentations.title')}
                subtitle={t('admin.documentations.subtitle')}>
                    <AdminContainer>
                        <Input
                            className="cell medium-6 align-center-middle"
                            labelText={t('admin.label.which')}
                            value={this.state.searchPatchByDescription}
                            onChange={this.searchPatchByDescription}
                            placeholder={t('admin.documentations.search_by_description')}
                            type="text"/>
                        <AdminList
                            title={t('admin.documentations.list_update_title')}
                            listLength={listDocumentEvo.length}
                            labelButton={t('admin.documentations.add_document')}
                            addLink={"/admin/documentation/mise-a-jour"}
                            headTitles={[t('common.label.description'), t('common.label.date'), t('common.label.version'), t('common.label.actions')]}
                            emptyListMessage={t('common.no_results', {name: t('admin.documentations.name'), context: 'female'})}>
                                {listDocumentEvo}
                        </AdminList>
                        <Input
                            className="cell medium-6 align-center-middle"
                            labelText={t('admin.label.which')}
                            value={this.state.searchHelpByDescription}
                            onChange={this.searchHelpByDescription}
                            placeholder={t('admin.documentations.search_by_description')}
                            type="text"/>
                        <AdminList
                            title={t('admin.documentations.list_help_title')}
                            listLength={listDocumentHelp.length}
                            labelButton={t('admin.documentations.add_document')}
                            addLink={"/admin/documentation/aide"}
                            headTitles={[t('common.label.description'), t('common.label.date'), t('common.label.actions')]}
                            emptyListMessage={t('common.no_results', {name: t('admin.documentations.name'), context: 'female'})}>
                                {listDocumentHelp}
                        </AdminList>
                    </AdminContainer>
            </AdminPage>
        )
    }

}

export default translate(['sesile'])(Documentations)

const RowDocumentEvo = ({patch, deletePatch}, {t}) => {
    return(
        <AdminListRow>
            <Cell className="medium-auto">
                <span title={patch.description}>
                    {(patch.description.length > 20) ?
                        `${patch.description.substring(0, 20)}...` :
                        patch.description
                    }
                </span>
            </Cell>
            <Cell className="medium-auto">
                {Moment(patch.date).format('LL')}
            </Cell>
            <Cell className="medium-auto">
                {patch.version}
            </Cell>
            <Cell className="medium-auto">
                <GridX>
                    <Cell className="medium-auto">
                        <i 
                            className="fi-pencil medium icon-action" 
                            title={t('common.button.edit')} 
                            onClick={() => History.push(`/admin/documentation/mise-a-jour/${patch.id}`)} >
                        </i>
                    </Cell>
                    <Cell className="medium-auto">
                        <Link 
                            to={
                                Routing.generate(
                                    'sesile_main_documentationapi_showdocumentpatch', 
                                    {id: patch.id})}
                            target="_blank"
                            className="fi-page-pdf medium icon-action" 
                            title={t('common.consult_document')}>
                        </Link>
                    </Cell>
                    <Cell className="medium-auto">
                        <ButtonConfirmDelete
                            id={patch.id}
                            dataToggle={`delete-confirmation-update-${patch.id}`}
                            onConfirm={deletePatch}
                            content={t('common.confirm_deletion_item')} />
                    </Cell>
                </GridX>
            </Cell>
        </AdminListRow>
    )
}

RowDocumentEvo.contextTypes = {
    t: func
}

const RowDocumentHelp = ({help, deleteHelp}, {t}) => {
    return(
        <AdminListRow>
            <Cell className="medium-auto">
                <span title={help.description}>
                    {(help.description.length > 40) ?
                        `${help.description.substring(0, 40)}...` :
                        help.description
                    }
                </span>
            </Cell>
            <Cell className="medium-auto">
                {Moment(help.date).format('LL')}
            </Cell>
            <Cell className="medium-auto">
                <GridX>
                    <Cell className="medium-auto">
                        <i 
                            className="fi-pencil medium icon-action" 
                            title={t('common.button.edit')} 
                            onClick={() => History.push(`/admin/documentation/aide/${help.id}`)} >
                        </i>
                    </Cell>
                    <Cell className="medium-auto">
                        <Link 
                            to={
                                Routing.generate(
                                    'sesile_main_documentationapi_showdocumentaide', 
                                    {id: help.id})}
                            target="_blank"
                            className="fi-page-pdf medium icon-action" 
                            title={t('common.consult_document')}>
                        </Link>
                    </Cell>
                    <Cell className="medium-auto">
                        <ButtonConfirmDelete
                            id={help.id}
                            dataToggle={`delete-confirmation-help-${help.id}`}
                            onConfirm={deleteHelp}
                            content={t('common.confirm_deletion_item')} />
                    </Cell>
                </GridX>
            </Cell>
        </AdminListRow>
    )
}

RowDocumentHelp.contextTypes = {
    t: func
}