import React, { Component } from 'react'
import { object, func } from 'prop-types'
import { translate } from 'react-i18next'
import Validator from 'validatorjs'
import { escapedValue } from '../_utils/Search'
import SelectCollectivite from '../_components/SelectCollectivite'
import InputValidation from '../_components/InputValidation'
import { GridX, Cell } from '../_components/UI'
import { Form } from '../_components/Form'

class Types extends Component {

    static contextTypes = {
        t: func
    }

    constructor(props) {
        super(props)
        this.state = {
            isSuperAdmin: false,
            types: [],
            filteredTypes: [],
            typesId: null,
            currentCollectiviteId: 0,
            userRoles: '',
            searchFieldName: '',
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
        this.setState({currentCollectiviteId: user.collectivite.id})
        if(user.roles.includes("ROLE_SUPER_ADMIN")) this.setState({isSuperAdmin: true})
    }

    fetchTypes = (id) => {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_getall', {id}), { credentials: 'same-origin'})
            .then(response => response.json())
            .then(json => this.setState({types: json, filteredTypes: json}))
            .then(() => {if(this.state.searchFieldName) this.handleChangeSearchByName(this.state.searchFieldName)})
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
                    collectivites: this.state.currentCollectiviteId,
                }),
                credentials: 'same-origin'
                })
                .then(response => response.json())
                .then(json => {
                    let filteredTypes = this.state.filteredTypes
                    filteredTypes.push(json)
                    this.setState({filteredTypes, nom: ''})
            })
        }
    }

    updateType = (id, nom) => {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_updatetypeclasseur', {id}), {
            method: 'put',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                nom: nom,
                collectivites: this.state.currentCollectiviteId,
            }),
            credentials: 'same-origin'
            })
            .then(response => response.json())
            .then((json) => {
                const filteredTypes = this.state.filteredTypes
                filteredTypes.map(filteredType => {if (filteredType.id === id) filteredType = json})
                this.setState({filteredTypes})
            })
    }

    removeType = (id) => {
        fetch(Routing.generate('sesile_classeur_typeclasseurapi_remove', {id}), {
            method: 'delete',
            credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(() => this.fetchTypes(this.state.currentCollectiviteId))
    }

    handleChangeSearchByName = (searchFieldName) => {
        this.setState({searchFieldName})
        const regex = escapedValue(searchFieldName, this.state.filteredTypes, this.state.types)
        const filteredTypes = this.state.types.filter(type => regex.test(type.nom))
        this.setState({filteredTypes})
    }

    handleChangeCollectivite = (currentCollectiviteId) => {
        this.setState({currentCollectiviteId})
        this.fetchTypes(currentCollectiviteId)
    }

    handleChangeNameFields = (id, value) => {
        const filteredTypes = this.state.filteredTypes
        filteredTypes.map(filteredType => {if (filteredType.id === id) filteredType.nom = value})
        this.setState({filteredTypes})
    }

    handleChangeAddField = (field, value) => {
        this.setState({nom: value})
        const validation = new Validator({nom: value}, this.validationRules)
        if(validation.passes()) this.setState({disabledButtonAdd: false})
        else this.setState({disabledButtonAdd: true})
    }

    render() {
        const { t } = this.context
        const { filteredTypes, isSuperAdmin, currentCollectiviteId } = this.state
        const listType = filteredTypes.map(type => <TypeRow key={type.id}
                                                            type={type}
                                                            removeType={this.removeType}
                                                            updateType={this.updateType}
                                                            handleChangeNameFields={this.handleChangeNameFields}/>)
        return (
            <div>
                <h4 className="text-center text-bold">{t('admin.title', {name: t('admin.type.complet_name')})}</h4>
                <p className="text-center">{t('admin.subtitle')}</p>
                <div className="grid-x align-center-middle">
                    <div className="cell medium-6">
                        <div className="grid-x grid-padding-x align-center-middle">
                            <div className="medium-auto cell">
                                <label htmlFor="circuit_name_search">{t('admin.label.which')}</label>
                                <input id="type_name_search"
                                   value={this.state.searchFieldName}
                                   onChange={(event) => this.handleChangeSearchByName(event.target.value)}
                                   placeholder={t('admin.placeholder.type_name', {name: t('admin.type.name')})}
                                   type="text" />
                            </div>
                            {(isSuperAdmin) &&
                                <div className="medium-auto cell">
                                    <SelectCollectivite currentCollectiviteId={currentCollectiviteId} 
                                                        handleChange={this.handleChangeCollectivite} />
                                </div>
                            }

                        </div>
                    </div>
                    <div className="cell medium-8">
                        <div className="grid-x grid-padding-x panel">
                            <div className="cell medium-12 panel-heading grid-x">
                                <div className="cell medium-12">{t('admin.type.complet_name')}</div>
                            </div>
                            <Cell className="add-type">
                                <Form onSubmit={this.createType}>
                                    <GridX>
                                        <Cell className="medium-6">
                                            <InputValidation    id="nom"
                                                                type="text"
                                                                labelText={t('common.label.name')}
                                                                helpText={t('admin.type.help_text_add_type')}
                                                                value={this.state.nom} 
                                                                onChange={this.handleChangeAddField}
                                                                validationRule={this.validationRules.nom}
                                                                placeholder={t('admin.placeholder.name', { name: t('admin.type.name')})}/>
                                        </Cell>
                                        <Cell className="medium-6 text-right">
                                            <button className="button primary text-uppercase" disabled={this.state.disabledButtonAdd} onClick={() => this.createType()}>
                                                {t('admin.button.save', {name: t('admin.type.name')})}
                                            </button>
                                        </Cell>
                                    </GridX>
                                </Form>
                            </Cell>
                            {(listType.length > 0) ? listType :
                                <div className="cell medium-12 panel-body">
                                    <div className="text-center">
                                        {t('common.no_results', {name: t('admin.type.name')})}
                                    </div>
                                </div>
                            }
                        </div>
                    </div>
                </div>
            </div>
        )
    }

}

Types.PropTypes = {
    user: object.isRequired
}

export default translate(['sesile'])(Types)

const TypeRow = ({type, removeType, updateType, handleChangeNameFields}, {t}) => {
    return (
        <div className="cell medium-12 panel-body grid-x">
            <div className="cell medium-auto">
                {type.supprimable ?
                    <input  type="text"
                            value={type.nom}
                            onChange={(e) => handleChangeNameFields(type.id, e.target.value)} /> :
                    <span className="text-uppercase">{type.nom}</span> 
                }
            </div>
            <div className="cell medium-auto text-right">
                {type.supprimable &&
                    <button className="button primary text-uppercase"
                            onClick={() => updateType(type.id, type.nom)}>
                        {t('common.button.save')}
                    </button>
                }
            </div>
            {(type.supprimable) &&
                <div className="cell medium-auto text-right">
                    <button className="button alert text-uppercase"
                            onClick={() => removeType(type.id)}>
                        {t('common.button.delete')}
                    </button>
                </div>
            }
        </div>
    )
}

TypeRow.PropTypes = {
    type: object.isRequired,
    removeType: func.isRequired,
    updateType: func.isRequired,
    handleChangeNameFields: func.isRequired
}

TypeRow.contextTypes = {
    t: func
}