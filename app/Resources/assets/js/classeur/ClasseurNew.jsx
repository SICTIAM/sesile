import React, { Component } from 'react'
import {func, object} from 'prop-types'
import {Button, Form, Select, Textarea} from '../_components/Form'
import Moment from 'moment/moment'
import Validator from 'validatorjs'
import { translate } from 'react-i18next'
import InputValidation from '../_components/InputValidation'
import CircuitValidationSteps from '../circuit/CircuitValidationSteps'
import {arrayMove} from 'react-sortable-hoc'
import {handleErrors} from '../_utils/Utils'
import {basicNotification} from '../_components/Notifications'
import DocumentsNew from '../document/DocumentsNew'
import UsersCopy from './UsersCopy'
import History from '../_utils/History'

class ClasseurNew extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func
    }

    state = {
        edit: true,
        circuits: [],
        circuit: {},
        type:{},
        classeur: {
            nom: '',
            validation: Moment(),
            visibility: 3,
            description: ''
        },
        user: {},
        users_copy: [],
        documents: []
    }

    validationRules = {
        nom: 'required',
        validation: 'required'
    }

    componentDidMount() {
        this.getCircuitsValidation()
        fetch(Routing.generate('sesile_user_userapi_getcurrent'), { credentials: 'same-origin' })
            .then(response => response.json())
            .then(user => this.setState({user}))
    }

    getCircuitsValidation() {
        const { t, _addNotification} = this.context

        fetch(Routing.generate('sesile_user_circuitvalidationapi_listbyuser'), {credentials: 'same-origin'})
            .then(response => response.json())
            .then(circuits => {
                if (circuits.length === 0) {
                    _addNotification(basicNotification(
                        'error',
                        t('common.classeurs.error.no_circuits')))
                    History.push(`/`)
                }
                this.setState({circuits: circuits, circuit: circuits[0], type:circuits[0].types[0]})
            })
    }

    validationForm () {
        const { classeur, documents, type } = this.state
        const { t, _addNotification } = this.context
        const fields = {
            nom: classeur.nom,
            validation: Moment(classeur.validation).format('YYYY-MM-DD HH:mm')
        }
        const validation = new Validator(fields, this.validationRules)

        if (validation.passes()
            && ((documents.length === 1 && type.nom === "Helios") || (type.nom !== "Helios" && documents.length > 0))) {
            return true
        }

        _addNotification(basicNotification(
            'error',
            t('admin.error.add', {name: t('common.classeurs.name'), errorCode: t('common.classeurs.error.missing_input')})))
        return false

    }

    saveClasseur = () => {
        this.validationForm() && this.postClasseur()
    }

    postClasseur () {
        const { classeur, documents, user, type, circuit, users_copy } = this.state
        const etape_classeurs = circuit.etape_groupes
        let formData  = new FormData()

        documents.map((document) => (formData.append('documents[]', document)))
        users_copy.map((user_copy) => (formData.append('copy[]', user_copy.value)))

        etape_classeurs.map((etape_classeur, key) => {
            formData.append("etapeClasseurs[" + key + "][ordre]", etape_classeur.ordre)
            etape_classeur.users.map(user => {
                formData.append("etapeClasseurs[" + key + "][users][]", user.id)
            })
            etape_classeur.user_packs.map(user_pack => {
                formData.append("etapeClasseurs[" + key + "][user_packs][]", user_pack.id)
            })
        })

        formData.append('nom', classeur.nom)
        formData.append('validation', Moment(classeur.validation).format('YYYY-MM-DD HH:mm'))
        formData.append('description', classeur.description)
        formData.append('visibilite', classeur.visibility)
        formData.append('user', user.id)
        formData.append('type', type.id)
        formData.append('circuit_id', circuit.id)

        fetch(Routing.generate('sesile_classeur_classeurapi_post'), {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
            .then(handleErrors)
            .then(response => response.json())
            .then(classeur => {
                History.push(`/classeur/${classeur.id}`)
            })
            .catch(error => this.context._addNotification(basicNotification(
                'error',
                this.context.t('common.classeurs.error.post', {errorCode: error.status}),
                error.statusText)))
    }

    handleChangeCircuit = (name, value) => this.setState(prevState => {prevState.circuit = this.state.circuits.find(circuit => circuit.id === parseInt(value))})
    handleChangeType = (name, value) => this.setState(prevState => {prevState.type = this.state.circuit.types.find(type => type.id === parseInt(value))})
    handleChangeClasseur = (key, value) => this.setState(prevState => {prevState.classeur[key] = value})
    handleChangeLimitDate = (date) => this.handleChangeClasseur('validation', date)
    handleClickAddStep = () => this.setState(prevState => prevState.circuit.etape_groupes.push({ordre: this.state.circuit.etape_groupes.length, user_packs:[], users:[]}))
    handleClickDeleteStep = (stepKey) => {
        this.setState(prevState => {
            prevState.circuit.etape_groupes.forEach((etape_groupe, key) => {if(key > stepKey) etape_groupe.ordre-- })
            {prevState.circuit.etape_groupes.splice(stepKey,1)}
        })
    }
    handleClickDeleteUser = (stepKey, userKey) => this.setState(prevState => prevState.circuit.etape_groupes[stepKey].users.splice(userKey, 1))
    handleClickDeleteGroup = (stepKey, groupId) => this.setState(prevState => prevState.circuit.etape_groupes[stepKey].user_packs.splice(groupId, 1))
    addGroup = (stepKey, group) => this.setState(prevState => prevState.circuit.etape_groupes[stepKey].user_packs.push(group))
    addUser = (stepKey, user) => this.setState(prevState => prevState.circuit.etape_groupes[stepKey].users.push(user))
    onSortEnd = ({oldIndex, newIndex}) => {
        let { circuit } = this.state
        circuit.etape_groupes = arrayMove(circuit.etape_groupes, oldIndex, newIndex)
        this.setState(prevState => prevState.circuit.etape_groupes.forEach((etape_groupe, key) => { etape_groupe.ordre = key }))
        this.setState({circuit})
    }
    handleSelectChange = (users_copy) => this.setState({ users_copy })
    onDrop = (documents) => this.setState(prevState => prevState.documents = [...this.state.documents, ...documents])
    removeDocument = (key) => this.setState(prevState => prevState.documents.splice(key, 1))

    render() {
        const { circuits, circuit, type, classeur, user, documents, users_copy } = this.state
        const { t } = this.context
        const { i18nextLng } = window.localStorage
        const listCircuits = circuits.map(circuit => <option key={circuit.id} value={circuit.id}>{circuit.nom}</option>)

        let listTypes
        circuit.types ?
            listTypes = circuit.types.map(type => <option key={type.id} value={type.id}>{type.nom}</option>)
            : listTypes = ""

        return(
            <div className="grid-y grid-frame">
                <div className="cell medium-12 grid-y">


                    <div className="grid-x medium-12 grid-margin-x grid-padding-x">

                        <div className="cell medium-8 cell-block-y details-classeur">

                            <div className="grid-x grid-margin-x grid-padding-x">
                                <div className="cell medium-12">
                                    <h1>{t('common.classeurs.title_add')}</h1>
                                </div>
                            </div>
                            { circuit.id &&
                                <Form onSubmit={this.saveClasseur}>
                                    <div className="grid-x grid-margin-x grid-padding-x">
                                        <Select id="circuits"
                                                className="cell medium-6"
                                                label={t('common.classeurs.label.circuits')}
                                                value={circuit.id}
                                                onChange={this.handleChangeCircuit}
                                                children={listCircuits}
                                        />
                                        <Select id="types"
                                                className="cell medium-6"
                                                label={t('common.classeurs.label.types')}
                                                value={type.id}
                                                onChange={this.handleChangeType}
                                                children={listTypes}
                                        />
                                    </div>
                                    <div className="grid-x grid-margin-x grid-padding-x">
                                        <InputValidation    id="nom"
                                                            type="text"
                                                            className="cell medium-6"
                                                            labelText={t('common.label.name')}
                                                            value={classeur.nom}
                                                            onChange={this.handleChangeClasseur}
                                                            validationRule={this.validationRules.nom}
                                                            placeholder={t('common.classeurs.classeur_name')}
                                        />
                                        <InputValidation   id="validation"
                                                           type="date"
                                                           className="cell medium-6"
                                                           value={Moment(classeur.validation)}
                                                           labelText={t('common.classeurs.date_limit')}
                                                           readOnly={true}
                                                           locale={i18nextLng}
                                                           validationRule={this.validationRules.validation}
                                                           onChange={this.handleChangeLimitDate}
                                                           minDate={Moment()}
                                        />
                                    </div>

                                    <div className="grid-x grid-margin-x grid-padding-x">
                                        <Visibility visibility={classeur.visibility} handleChangeClasseur={this.handleChangeClasseur} />
                                    </div>

                                    <div className="grid-x grid-margin-x grid-padding-x">
                                        <Textarea id="classeur-description"
                                                  name="description"
                                                  className="cell medium-12"
                                                  labelText={t('common.label.description')}
                                                  value={classeur.description}
                                                  onChange={this.handleChangeClasseur}/>
                                    </div>

                                    <div className="grid-x grid-margin-x grid-padding-x">
                                        <div className="cell medium-12">
                                            <div className="grid-x">
                                                <div className="cell medium-12">
                                                    <h2>{t('admin.circuit.complet_name')}</h2>
                                                </div>
                                            </div>
                                            <div className="grid-x">
                                                <div className="cell medium-12">
                                                    { (circuit.etape_groupes && user.collectivite) &&
                                                    <CircuitValidationSteps  steps={Object.assign([], circuit.etape_groupes)}
                                                                             collectiviteId={user.collectivite.id}
                                                                             onSortEnd={this.onSortEnd}
                                                                             handleClickDeleteUser={this.handleClickDeleteUser}
                                                                             handleClickDeleteGroup={this.handleClickDeleteGroup}
                                                                             handleClickDeleteStep={this.handleClickDeleteStep}
                                                                             handleClickAddStep={this.handleClickAddStep}
                                                                             addUser={this.addUser}
                                                                             addGroup={this.addGroup}
                                                    />
                                                    }
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="grid-x grid-margin-x grid-padding-x">
                                        <div className="cell medium-12">
                                            <div className="grid-x">
                                                <div className="cell medium-12">
                                                    <h2>{t('common.classeurs.users_copy')}</h2>
                                                </div>
                                            </div>
                                            <div className="grid-x">
                                                    {
                                                        (user.collectivite && user.collectivite.id) &&
                                                        <UsersCopy currentCollectiviteId={user.collectivite.id}
                                                                   handleChange={this.handleSelectChange}
                                                                   className="cell medium-12"
                                                                   users_copy={users_copy}
                                                        />
                                                    }
                                            </div>
                                        </div>
                                    </div>

                                    <div className="grid-x grid-margin-x grid-padding-x grid-margin-y grid-padding-y">
                                        <div className="cell medium-12">
                                            <Button id="submit-classeur-infos"
                                                    className="cell medium-12"
                                                    classNameButton="float-right"
                                                    onClick={this.saveClasseur}
                                                    labelText={t('common.button.save')}
                                                    disabled={!this.state.edit}
                                            />
                                        </div>
                                    </div>

                                </Form>
                            }
                        </div>


                        <div className="cell medium-4 cell-block-y details-classeur">
                            <div className="grid-x grid-padding-y">
                                <div className="cell medium-12">
                                    <div className="grid-x">
                                        <h3 className="cell medium-12">{t('common.documents.title_preview')}</h3>
                                    </div>

                                    <DocumentsNew documents={ Object.assign([],documents) }
                                                  onDrop={this.onDrop}
                                                  removeDocument={this.removeDocument}
                                                  typeClasseur={ type }
                                    />
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        )
    }
}

export default translate('sesile')(ClasseurNew)


const Visibility = ({visibility, handleChangeClasseur}, {t}) => {

    const visibilitiesStatus = ["Privé", "Public", "Privé a partir de moi", "Service organisationnel"]
    const listVisibilities = visibilitiesStatus.map((visibility, key) =>
        <option key={key} value={key}>{visibility}</option>
    )

    return (
        <Select id="visibility"
                className="cell medium-6"
                label={t('common.label.visibility')}
                value={visibility}
                onChange={handleChangeClasseur}
                children={listVisibilities}
        />
    )
}

Visibility.contextTypes = {
    t: func
}