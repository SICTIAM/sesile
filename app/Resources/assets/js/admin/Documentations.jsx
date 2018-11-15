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
import { handleErrors, DisplayLongText } from '../_utils/Utils'
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
        filteredHelps: [],
        filetered : [],
        patchhelp: []
    }

    componentDidMount() {
        this.fetchHelps()
        this.fetchPatchs()
    }
    componentDidUpdate() {
        if (this.state.filteredHelps.length > 0 && this.state.filteredPatchs.length > 0 && this.state.patchhelp.length === 0) {
           this.concatHelpPatch()
        }
    }
    concatHelpPatch() {
        const patchhelp = []
        this.state.filteredHelps.map((help) => patchhelp.push(help))
        this.state.filteredPatchs.map((help) => patchhelp.push(help))
        patchhelp.sort((a, b) =>{
            const dateA = new Date(a.date)
            const dateB = new Date(b.date)
            return dateB - dateA
        })
        this.setState({patchhelp: patchhelp, filetered: patchhelp})
    }
    fetchHelps = () => {
        const {t, _addNotification} = this.context
        fetch(Routing.generate('sesile_main_documentationapi_getallaide'), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(helps => {
                this.setState({helps, filteredHelps: helps})
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {
                    name: t('common.help_board.title_helps'),
                    errorCode: error.status
                }),
                error.statusText)))
    }
    fetchPatchs = () => {
        const {t, _addNotification} = this.context
        fetch(Routing.generate('sesile_main_documentationapi_getallpatch'), {credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(patchs => {
                this.setState({patchs, filteredPatchs: patchs})
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.error.not_extractable_list', {
                    name: t('common.help_board.title_patchs'),
                    errorCode: error.status
                }),
                error.statusText)))
    }
    deleteHelp = (id) => {
        const {t, _addNotification} = this.context
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
            .then(help => this.concatHelpPatch())
            .catch(error => _addNotification(
                basicNotification(
                    'error',
                    t('admin.documentations.error.delete'),
                    error.statusText)))
    }
    deletePatch = (id) => {
        const {t, _addNotification} = this.context
        fetch(Routing.generate('sesile_main_documentationapi_removepatch', {id}), {
            method: 'DELETE',
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(patchs => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.documentations.succes_delete')))
                this.setState({patchs, filteredPatchs: patchs})
            })
            .then(patch => this.concatHelpPatch())
            .catch(error => _addNotification(
                basicNotification(
                    'error',
                    t('admin.documentations.error.delete'),
                    error.statusText)))
    }
    searchByDescription = (key, searchPatchByDescription) => {
        this.setState({searchPatchByDescription})
        const regex = escapedValue(searchPatchByDescription, this.state.filetered, this.state.patchhelp)
        const filteredPatchs = this.state.patchhelp.filter(patch => regex.test(patch.description))
        this.setState({filetered: filteredPatchs})
    }
    onClickAction = (e) => {
        e.stopPropagation()
    }

    render() {
        const {t} = this.context
        const {filteredHelps, filteredPatchs, patchhelp, filetered} = this.state
        const listDocumentEvo = filetered.map((patch) => <RowDocumentEvo key={patch.id} patch={patch}
                                                                         onClickAction={this.onClickAction}
                                                                         deleteHelp={this.deleteHelp}
                                                                         deletePatch={this.deletePatch}/>)
        return (
            <AdminPage>
                <div className="cell medium-12 text-center">
                    <h2>{t('admin.documentations.title')}</h2>
                </div>
                <AdminContainer>
                    <div className="grid-x grid-padding-x panel align-center-middle"
                         style={{width: "74em", marginTop: "1em"}}>
                        <div className="cell medium-12 grid-x panel align-center-middle"
                             style={{display: "flex", marginBottom: "0em", marginTop: "10px", width: "50%"}}>
                            <div style={{marginTop:"10px", width:"100%"}}>
                            <Input
                                className="cell medium-6 align-center-middle"
                                value={this.state.searchPatchByDescription}
                                onChange={this.searchByDescription}
                                placeholder={t('admin.documentations.search_by_description')}
                                type="text"/>
                            </div>
                        </div>
                        <div className="cell medium-12 text-right"  style={{marginTop:"10px"}}>
                            <button className="button hollow"
                                    onClick={() => History.push("/admin/documentation")}>{t('admin.documentations.add_document')}</button>
                        </div>
                        <table style={{margin: "10px", borderRadius: "6px"}}>
                            <thead>
                            <tr style={{backgroundColor: "#CC0066", color: "white"}}>
                                <td width="600px" className="text-bold">{t('admin.user.label_name')}</td>
                                <td width="100px" className="text-bold">Types</td>
                                <td width="120px" className="text-bold">{t('common.label.date')}</td>
                                <td width="50" className="text-bold">{t('common.label.version')}</td>
                                <td width="30px" className="text-bold">{t('common.label.actions')}</td>
                            </tr>
                            </thead>
                            <tbody>
                            {filetered.length > 0 ?
                                listDocumentEvo :
                                <tr>
                                    <td>
                                        <span
                                            style={{textAlign: "center"}}>{t('common.no_results', {name: t('admin.type.name')})}</span>
                                    </td>
                                    <td/>
                                    <td/>
                                    <td/>
                                    <td/>
                                </tr>}
                            </tbody>
                        </table>
                    </div>
                </AdminContainer>
            </AdminPage>
        )
    }

}

export default translate(['sesile'])(Documentations)

const RowDocumentEvo = ({patch, onClickAction, deletePatch, deleteHelp}, {t}) => {
    return(
        <tr onClick={() => patch.version ?  History.push(`/admin/documentation/mise-a-jour/${patch.id}`) :  History.push(`/admin/documentation/aide/${patch.id}`)} style={{cursor:"pointer"}}>
            <td>
                <DisplayLongText text={patch.description} maxSize={100} />
            </td>
            <td>
                {patch.version ? "Patch" : "Aide" }
            </td>
            <td>
                {Moment(patch.date).format('LL')}
            </td>
            <td>
                {patch.version && patch.version}
            </td>
            <td onClick={(e) => onClickAction(e)}>
                <GridX>
                    <Cell className="medium-auto">
                        <Link
                            to={
                                Routing.generate(`sesile_main_documentationapi_showdocument${patch.version ? "patch": "aide"}`,
                                    {id: patch.id})}
                            target="_blank"
                            className="fa fa-file-pdf-o icon-action"
                            title={t('common.consult_document')}>
                        </Link>
                    </Cell>
                    <Cell className="medium-auto">
                        <ButtonConfirmDelete
                            id={patch.id}
                            dataToggle={`delete-confirmation-${patch.version ? "update": "help"}-${patch.id}`}
                            onConfirm={patch.version ? deletePatch : deleteHelp}
                            content={t('common.confirm_deletion_item')} />
                    </Cell>
                </GridX>
            </td>
        </tr>
    )
}

RowDocumentEvo.contextTypes = {
    t: func
}