import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { translate } from 'react-i18next'
import Validator from 'validatorjs'

import { AdminList, AdminPage, AdminContainer, AdminListRow } from "../_components/AdminUI"
import { Form, Input } from '../_components/Form'
import InputValidation from '../_components/InputValidation'
import { GridX, Cell } from '../_components/UI'
import SelectCollectivite from '../_components/SelectCollectivite'

import { escapedValue } from '../_utils/Search'
import { handleErrors } from "../_utils/Utils"

import Type from './Type'
import { basicNotification } from "../_components/Notifications"

class Types extends Component {
    static contextTypes = {
        t: func,
        _addNotification: func
    }
    constructor(props) {
        super(props)
        this.state = {
            isSuperAdmin: false,
            types: [],
            filteredTypes: [],
            editableTypes: [],
            filteredEditableTypes: [],
            typesId: null,
            collectiviteId: null,
            searchByName: '',
            nom: '',
            disabledButtonAdd: true
        }
    }
    validationRules = {
        nom: 'required'
    }
    componentDidMount() {
        const user = this.props.user
        this.fetchTypes(user.collectivite.id)
        this.setState({collectiviteId: user.collectivite.id})
        if(user.roles.includes("ROLE_SUPER_ADMIN")) this.setState({isSuperAdmin: true})
    }
    fetchTypes = (id) => {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_getall', {id}), { credentials: 'same-origin'})
        .then(handleErrors)
        .then(response => response.json())
        .then(json => {
            const editableTypes = []
            const types = []
            this.setState({types, editableTypes, filteredEditableTypes: editableTypes})
            json.map(type => type.supprimable ? editableTypes.unshift(type) : types.unshift(type))
            this.setState({types, editableTypes, filteredEditableTypes: editableTypes})
        })
        .catch(() =>
            this.context._addNotification(
                basicNotification(
                    'error',
                    this.context.t('admin.type.error_fetch_list'))))
    }
    createType = () => {
        const validation = new Validator({nom: this.state.nom}, this.validationRules)
        if(validation.passes()) {
            fetch(Routing.generate('sesile_classeur_typeclasseurapi_posttypeclasseur'), {
                method: 'post',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    nom: this.state.nom,
                    collectivites: this.state.collectiviteId,
                }),
                credentials: 'same-origin'
            })
            .then(handleErrors)
            .then(response => response.json())
            .then(() => {
                this.setState({nom: ''})
                this.context._addNotification(
                    basicNotification(
                        'success',
                        this.context.t('admin.type.success_save')))
                this.fetchTypes(this.state.collectiviteId)})
            .catch(() =>
                this.context._addNotification(
                    basicNotification(
                        'error',
                        this.context.t('admin.type.error_save'))))
        }
    }
    handleChangeSearchByName = (key, searchByName) => {
        this.setState({searchByName})
        const regex = escapedValue(searchByName, this.state.filteredTypes, this.state.types)
        const filteredEditableTypes = this.state.editableTypes.filter(type => regex.test(type.nom))
        this.setState({filteredEditableTypes})
    }
    handleChangeCollectivite = (collectiviteId) => {
        this.setState({collectiviteId, searchByName: ''})
        this.fetchTypes(collectiviteId)
    }
    handleChangeAddField = (field, value) => {
        this.setState({nom: value})
        const validation = new Validator({nom: value}, this.validationRules)
        if(validation.passes()) this.setState({disabledButtonAdd: false})
        else this.setState({disabledButtonAdd: true})
    }
    render() {
        const { t } = this.context
        const listEditableType =
            this.state.filteredEditableTypes.map(type =>
                <Type
                    key={type.id}
                    collectiviteId={this.state.collectiviteId}
                    type={type}
                    removeType={this.removeType}
                    fetchTypes={this.fetchTypes}/>)
        const listNotEditableType =
            this.state.types.map(type =>
                <AdminListRow key={type.id}>
                    <Cell className="medium-auto">
                        {type.nom}
                    </Cell>
                </AdminListRow>)
        return (
            <AdminPage
                title={t('admin.title', {name: t('admin.type.name')})}
                subtitle={t('admin.subtitle')}>
                <AdminContainer>
                    <Cell className="medium-6">
                        <GridX className="grid-padding-x align-center-middle">
                            <Input
                                className="cell medium-auto"
                                labelText={t('admin.label.which')}
                                value={this.state.searchByName}
                                onChange={this.handleChangeSearchByName}
                                placeholder={t('common.search_by_name')}
                                type="text"/>
                            {this.state.isSuperAdmin &&
                                <Cell className="medium-auto">
                                    <SelectCollectivite
                                        currentCollectiviteId={this.state.collectiviteId}
                                        handleChange={this.handleChangeCollectivite}/>
                                </Cell>}
                        </GridX>
                    </Cell>
                    <AdminList
                        title={t('admin.type.title_add_type')}
                        listLength={1}
                        headTitles={[]}>
                        <Cell className="add-type">
                            <Form onSubmit={this.createType}>
                                <GridX className="align-middle">
                                    <Cell className="medium-6">
                                        <InputValidation
                                            id="nom"
                                            type="text"
                                            labelText={`${t('common.label.name')} *`}
                                            helpText={t('admin.type.help_text_add_type')}
                                            value={this.state.nom}
                                            onChange={this.handleChangeAddField}
                                            validationRule={this.validationRules.nom}
                                            placeholder={t('common.type_a_name')}/>
                                    </Cell>
                                    <Cell className="medium-6 text-right">
                                        <button
                                            className="button primary hollow text-uppercase"
                                            disabled={this.state.disabledButtonAdd}
                                            onClick={() => this.createType()}>
                                            {t('common.button.save')}
                                        </button>
                                    </Cell>
                                </GridX>
                            </Form>
                        </Cell>
                    </AdminList>
                    <AdminList
                        title={t('admin.type.list_editable_type')}
                        listLength={listEditableType.length}
                        headTitles={[t('common.label.name'), t('common.label.actions')]}
                        headGrid={['medium-auto', 'medium-2']}
                        emptyListMessage={t('common.no_results', {name: t('admin.type.name')})}>
                        {listEditableType}
                    </AdminList>
                    <AdminList
                        title={t('admin.type.list_not_editable_type')}
                        listLength={listNotEditableType.length}
                        headTitles={[t('common.label.name')]}
                        emptyListMessage={t('common.no_results', {name: t('admin.type.name')})}>
                        {listNotEditableType}
                    </AdminList>
                </AdminContainer>
            </AdminPage>
        )
    }

}

Types.PropTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Types)