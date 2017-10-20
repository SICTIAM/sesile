import React, { Component } from 'react'
import { func } from 'prop-types'
import { translate } from 'react-i18next'
import { AdminDetails } from "../_components/AdminUI"
import { basicNotification } from "../_components/Notifications"
import { handleErrors } from '../_utils/Utils'
import DocumentationHelp from "./DocumentationHelp"
import DocumentationHelpAdd from './DocumentationHelpAdd'
import DocumentationPatchAdd from './DocumentationPatchAdd'
import DocumentationPatch from "./DocumentationPatch";
import Moment from 'moment'


class Documentations extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    constructor(props) {
        super(props)
        this.state = {
            helps: [],
            patchs: []
        }
    }

    componentDidMount() {
        this.fetchHelps()
        this.fetchPatchs()
    }

    fetchHelps = () => {
        const { t, _addNotification } = this.context
        fetch(Routing.generate('sesile_main_documentationapi_getallaides'), { credentials: 'same-origin'})
        .then(handleErrors)
            .then(response => response.json())
            .then(helps => {
                this.setState({helps})
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
                this.setState({patchs})
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
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(() => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.success.delete', {name: t('admin.documentations.help')})
                ))
                this.fetchHelps()
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.documentations.error.delete_documentation', {errorCode: error.status}),
                error.statusText)))
    }

    deletePatch = (id) => {
        const { t, _addNotification } = this.context

        fetch(Routing.generate('sesile_main_documentationapi_removepatch', {id}), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(() => {
                _addNotification(basicNotification(
                    'success',
                    t('admin.success.delete', {name: t('admin.documentations.help')})
                ))
                this.fetchPatchs()
            })
            .catch(error => _addNotification(basicNotification(
                'error',
                t('admin.documentations.error.delete_documentation', {errorCode: error.status}),
                error.statusText)))
    }

    addHelp = (help) => {
        fetch(Routing.generate('sesile_main_documentationapi_postaide'), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                description: help.description,
                date: Moment(help.date).format('YYYY-MM-DD HH:mm')
            }),
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(() => {
                this.context._addNotification(basicNotification(
                    'success',
                    this.context.t('admin.documentations.success_add')))
                this.fetchHelps()
            })
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                this.context.t('admin.documentations.error.fail_add', {errorCode: error.status}),
                error.statusText)))
    }

    addPatch = (patch) => {
        fetch(Routing.generate('sesile_main_documentationapi_postpatch'), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                description: patch.description,
                version: patch.version,
                date: Moment(patch.date).format('YYYY-MM-DD HH:mm')
            }),
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(() => {
                this.context._addNotification(basicNotification(
                    'success',
                    this.context.t('admin.documentations.success_add')))
                this.fetchPatchs()
            })
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                this.context.t('admin.documentations.error.fail_add', {errorCode: error.status}),
                error.statusText)))
    }

    render() {
        const { t } = this.context
        const { helps, patchs } = this.state

        const rowHelp = helps.map((help, key) => <DocumentationHelp key={key} help={help} delete={ this.deleteHelp } />)
        const rowPatch = patchs.map((patch, key) => <DocumentationPatch key={key} patch={patch} delete={ this.deletePatch } />)


        return (
            <AdminDetails title={t('common.help_board.title')}
                          nom={t('common.help_board.title')}
                          subtitle={t('admin.details.subtitle')}>

                <div className="admin-content-details">
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-12 cell">
                            <h4>{t('common.help_board.title_helps')}</h4>
                        </div>
                    </div>
                    { rowHelp }
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-12 cell">
                            <h4>{t('admin.documentations.help_add')}</h4>
                        </div>
                    </div>
                    <DocumentationHelpAdd addHelp={ this.addHelp } />

                    <hr/>
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-12 cell">
                            <h4>{t('common.help_board.title_patchs')}</h4>
                        </div>
                    </div>
                    { rowPatch }
                    <div className="grid-x grid-margin-x grid-padding-x">
                        <div className="medium-12 cell">
                            <h4>{t('admin.documentations.patch_add')}</h4>
                        </div>
                    </div>
                    <DocumentationPatchAdd addPatch={ this.addPatch } />

                </div>
            </AdminDetails>
        )
    }

}

export default translate(['sesile'])(Documentations)