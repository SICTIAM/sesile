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
        this.fetchTypes(user.current_org_id)
        this.setState({collectiviteId: user.current_org_id})
        if(user.roles.includes("ROLE_SUPER_ADMIN")) this.setState({isSuperAdmin: true})
    }
    fetchTypes = (id) => {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_getall', {id}), { credentials: 'same-origin'})
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                const editableTypes = []
                const types = []
                this.setState({types, editableTypes: types, filteredEditableTypes: types})
                json.map(type => types.unshift(type))
                types.sort((a, b) => a.nom.localeCompare(b.nom))
                this.setState({types, editableTypes: types, filteredEditableTypes: types})
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
        return (
            <AdminPage>
                <div className="cell medium-12 text-center">
                    <h2>{t('admin.type.name')}</h2>
                </div>
                <AdminContainer>
                    <div className="grid-x grid-padding-x panel align-center-middle" style={{width:"74em", marginTop:"1em"}}>
                        <div className="grid-x grid-padding-x medium-6 panel align-center-middle" style={{display:"flex", marginBottom:"0em", marginTop:"10px", width:"49%"}}>
                            <div className="" style={{marginTop:"10px",paddingLeft: "1%", width:"17em", paddingRight:"1%"}}>
                                <Input
                                    className="cell medium-auto"
                                    value={this.state.searchByName}
                                    onChange={this.handleChangeSearchByName}
                                    placeholder={t('common.search_by_name')}
                                    type="text"/>
                            </div>
                            {this.state.isSuperAdmin &&
                            <div style={{marginTop:"10px",paddingLeft: "1%", width:"17em", paddingRight:"1%"}}>
                                <SelectCollectivite
                                    currentCollectiviteId={this.state.collectiviteId}
                                    handleChange={this.handleChangeCollectivite}/>
                            </div>}
                        </div>
                        <table style={{margin:"10px", borderRadius:"6px", width:"98%"}}>
                            <thead>
                            <tr style={{backgroundColor:"#CC0066", color:"white"}}>
                                <td width="600px" className="text-bold">{ t('admin.user.label_name') }</td>
                                <td width="30px" className="text-bold">{ t('common.label.actions') }</td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>
                                    <div style={{width:"50%"}}>
                                        <InputValidation
                                            id="nom"
                                            type="text"
                                            value={this.state.nom}
                                            onChange={this.handleChangeAddField}
                                            validationRule={this.validationRules.nom}
                                            placeholder={t('common.type_a_name')}/>
                                    </div>
                                </td>
                                <td> <button
                                    className="button primary hollow text-uppercase"
                                    disabled={this.state.disabledButtonAdd}
                                    onClick={() => this.createType()}>
                                    {t('common.button.add')}
                                </button> </td>
                            </tr>
                            {listEditableType.length > 0 ?
                                listEditableType :
                                <tr>
                                    <td>
                                        <span style={{textAlign:"center"}}>{t('common.no_results', {name: t('admin.type.name')})}</span>
                                    </td>
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

Types.PropTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Types)