import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { translate } from 'react-i18next'
import Validator from 'validatorjs'

import { AdminListRow } from "../_components/AdminUI"
import ButtonConfirmDelete from '../_components/ButtonConfirmDelete'
import InputValidation from '../_components/InputValidation'
import { GridX, Cell } from '../_components/UI'

import { handleErrors } from "../_utils/Utils"
import {basicNotification} from "../_components/Notifications"

class Type extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    state = {
        edit: false,
        type: {
            id: '',
            nom: ''
        }
    }
    validationRules = {
        nom: 'required'
    }
    componentDidMount() {
        this.setState({type: this.props.type})
    }
    handleChangeName = (id, value) => this.setState(prevState => {type: prevState.type.nom = value})
    handleClickUpdate = () => {
        const validation = new Validator({nom: this.state.type.nom}, this.validationRules)
        if(validation.passes()) {
            fetch(Routing.generate('sesile_classeur_typeclasseurapi_updatetypeclasseur', {id: this.state.type.id}), {
                method: 'PUT',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    nom: this.state.type.nom,
                    collectivites: this.props.collectiviteId,
                }),
                credentials: 'same-origin'
            })
            .then(handleErrors)
            .then(response => response.json())
            .then(() => {
                this.setState({edit: false})
                this.context._addNotification(
                    basicNotification(
                        'success',
                        this.context.t('admin.type.success_save')))
                this.props.fetchTypes(this.props.collectiviteId)
            })
            .catch(() =>
                this.context._addNotification(
                    basicNotification(
                        'error',
                        this.context.t('admin.type.error_save'))))
        }
    }
    handleClickRemove = () => {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_remove', {id: this.state.type.id}), {
            method: 'delete',
            credentials: 'same-origin'
        })
        .then(handleErrors)
        .then(response => response.json())
        .then(() => {
            this.context._addNotification(
                basicNotification(
                    'success',
                    this.context.t('admin.type.success_delete')))
            this.props.fetchTypes(this.props.collectiviteId)
        })
        .catch(() =>
            this.context._addNotification(
                basicNotification(
                    'error',
                    this.context.t('admin.type.error_delete'))))
    }
    handleClickEdit = () => this.setState(prevState => {edit: prevState.edit = !prevState.edit})
    handleClickCancel = () => {
        this.handleClickEdit()
        this.props.fetchTypes(this.props.collectiviteId)
    }
    render() {
        const { t } = this.context
        const { type } = this.state
        return (
            <AdminListRow>
                <Cell className="medium-auto">
                    <GridX>
                        <Cell className="medium-7">
                            {this.state.edit ?
                                <InputValidation
                                    id="nom"
                                    type="text"
                                    value={type.nom}
                                    validationRule={this.validationRules.nom}
                                    onChange={this.handleChangeName}
                                    autoFocus={this.state.edit}
                                    placeholder={t('common.type_a_name')}/> :
                                type.nom}
                        </Cell>
                    </GridX>
                </Cell>
                <Cell className="medium-2">
                    <GridX>
                        {this.state.edit &&
                            <Cell className="medium-auto">
                                <span
                                    onClick={() => this.handleClickCancel()}
                                    className="fa fa-undo icon-action"
                                    title={t('common.button.cancel')}/>
                            </Cell>}
                        <Cell className="medium-auto">
                            {this.state.edit ?
                                <span
                                    onClick={() => this.handleClickUpdate()}
                                    className="fa fa-check icon-action"
                                    title={t('common.button.save')}/> :
                                <span
                                    onClick={() => this.handleClickEdit()}
                                    className="fa fa-pencil icon-action"
                                    title={t('common.button.edit')}/>}
                        </Cell>
                        <Cell className="medium-auto">
                            <ButtonConfirmDelete
                                id={this.state.type.id}
                                dataToggle={`delete-confirmation-update-${this.props.type.id}`}
                                onConfirm={() => this.handleClickRemove()}
                                content={t('common.confirm_deletion_item')}/>
                        </Cell>
                    </GridX>
                </Cell>
            </AdminListRow>
        )
    }
}

export default translate(['sesile'])(Type)