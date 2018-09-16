import React, { Component } from 'react'
import { string, number, func, object }from 'prop-types'
import { translate } from 'react-i18next'

import { basicNotification } from '../_components/Notifications'
import { Select } from '../_components/Form'

import History from '../_utils/History'
import { handleErrors } from '../_utils/Utils'
import { refusClasseur, actionClasseur } from '../_utils/Classeur'

import ClasseursRow from './ClasseursRow'
import ClasseurPagination from './ClasseurPagination'
import ClasseursButtonList from './ClasseursButtonList'
import Moment from "moment";

class Classeurs extends Component {

    static contextTypes = {
        t: func,
        _addNotification: func,
        user: object
    }
    state = {
        classeurs: [],
        sort: "id",
        order: "DESC",
        limit: 15,
        start: 0,
        checkedAll: false,
        message: '',
        nbElement: 0,
        nbElementTotal: 0
    }
    statesCaption = [
        {color: '#c82d2e', state: 'refused'},
        {color: '#f48c4f', state: 'pending'},
        {color: '#39922c', state: 'finished'},
        {color: '#1c43a2', state: 'withdrawn'},
        {color: '#356bfc', state: 'retracted'}
    ]
    componentDidMount() {
        if(this.state.classeurs.length === 0) {
            this.listClasseurs(this.state.sort, this.state.order, this.state.limit, this.state.start, this.context.user.id)
        }
    }
    componentWillReceiveProps(nextProps) {

    }
    changeLimit = (name, value) => {
        this.setState({limit: parseInt(value)})
        this.listClasseurs(this.state.sort, this.state.order, value, this.state.start, this.context.user.id)
    }
    changePage = (start) => {
        const newStart = (start * this.state.limit)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.context.user.id)
    }
    changePreviousPage = () => {
        const newStart = (this.state.start - this.state.limit)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.context.user.id)
    }
    changeNextPage = () => {
        const newStart = (this.state.start + this.state.limit)
        this.listClasseurs(this.state.sort, this.state.order, this.state.limit, newStart, this.context.user.id)
    }
    listClasseurs = (sort, order, limit, start, userId) => {
        const { t, _addNotification } = this.context
        this.setState({message: t('common.loading')})
        fetch(Routing.generate(this.props.url, {orgId: this.context.user.current_org_id, sort, order, limit, start, userId}), { credentials: 'same-origin' })
            .then(handleErrors)
            .then(response => response.json())
            .then(json => {
                let classeurs = json.list.map(classeur =>
                    Object.defineProperty(classeur, "checked", {value : false, writable : true, enumerable : true, configurable : true}))
                this.setState({classeurs, nbElement: json.nb_element_in_list, nbElementTotal: json.nb_element_total_of_entity})
                $('#classeurRow').foundation()
                if(classeurs.length <= 0) this.setState({message: t('common.classeurs.empty_classeur_list')})
                else this.setState({message: null})
            })
            .then(this.setState({start}))
            .catch(error => {
                this.setState({message: t('common.error_loading_list')})
                _addNotification(basicNotification(
                    'error',
                    t('admin.error.not_extractable_list', {name: t('common.classeurs.name'), errorCode: error.status}),
                    error.statusText))
            })
    }
    checkAllClasseurs = () => {
        const newCheckAll = !this.state.checkedAll
        this.setState({checkedAll: newCheckAll})
        this.setState(prevState => prevState.classeurs.map(classeur => classeur.checked = newCheckAll))
    }
    checkClasseur = (event) => {
        const target = event.target
        this.setState(prevState => {
            const IndexOfClasseurInArray =
                prevState.classeurs.findIndex(classeur => classeur.id == target.id)
            const classeur = prevState.classeurs[IndexOfClasseurInArray]
            return classeur.checked = !classeur.checked
        })
    }
    validClasseurs = (classeurs) => { classeurs.map(classeur => { actionClasseur(this, 'sesile_classeur_classeurapi_validclasseur', classeur.id, 'PUT', 'list')})}
    signClasseurs = (classeurs) => {
        let ids
        ids = []
        classeurs.map(classeur => {
            ids.push(classeur.id)
        })
        History.push('/classeurs/previsualisation', {classeurs, user: this.context.user})
    }
    revertClasseurs = (classeurs) => { classeurs.map(classeur => { actionClasseur(this, 'sesile_classeur_classeurapi_retractclasseur', classeur.id, 'PUT', 'list')})}
    refuseClasseurs = (classeurs, motif) => { classeurs.map(classeur => { refusClasseur(this, 'sesile_classeur_classeurapi_refuseclasseur', classeur.id, motif, 'list') })}
    removeClasseurs = (classeurs) => { classeurs.map(classeur => { actionClasseur(this, 'sesile_classeur_classeurapi_removeclasseur', classeur.id, 'PUT', 'list') })}
    deleteClasseurs = (classeurs) => { classeurs.map(classeur => { actionClasseur(this, 'sesile_classeur_classeurapi_deleteclasseur', classeur.id, 'DELETE', 'list') })}

    render(){
        const { classeurs, limit, start, checkedAll } = this.state
        const { t } = this.context
        const limits = [15,30,50,100]
        const status = Object.freeze({
            0: 'refused',
            1: 'pending',
            2: 'finished',
            3: 'withdrawn',
            4: 'retracted'
        })
        const statusColorClass = Object.freeze({
            0: '#c82d2e',
            1: '#e2661d',
            2: '#2d6725',
            3: '#1c43a2',
            4: '#356bfc'
        })
        const listClasseur = this.state.classeurs.map(classeur =>
            <tr key={classeur.id}>
                <td>{classeur.nom}</td>
                <td>
                    <div
                        className={`ui label labelStatus`}
                        style={{color: '#fff', backgroundColor: statusColorClass[classeur.status], textAlign: 'center', width: '80px', padding: '5px', fontSize: '0.9em'}}>
                        {t(`common.classeurs.status.${status[classeur.status]}`)}
                    </div>
                </td>
                <td>
                    <Intervenants classeur={classeur}/>
                </td>
                <td>{Moment(classeur.validation).format('L')}</td>
                <td>{classeur.type.nom}</td>
                <td>
                    <ClasseursButtonList
                        classeurs={[classeur]}
                        validClasseur={this.validClasseurs}
                        signClasseur={this.signClasseurs}
                        revertClasseur={this.revertClasseurs}
                        refuseClasseur={this.refuseClasseurs}
                        removeClasseur={this.removeClasseurs}
                        deleteClasseur={this.deleteClasseurs}
                        id={classeur.id}
                        user={this.props.user}
                        check={this.checkClasseur}
                        checked={classeur.checked}
                        style={{fontSize: '0.8em'}}/>
                </td>
            </tr>)
        const listLimit = limits.map(limit =>
            <option key={limit} value={limit}>
                {limit}
            </option>)
        return (
            <div className="grid-x" style={{marginBottom: '10px'}}>
                <div className="cell medium-12 align-right">
                    <table>
                        <thead
                            style={{
                                background: "#3199cc",
                                color: "#fefefe"
                            }}>
                            <tr>
                                <th width="350" style={{borderRadius: "0.5rem 0 0 0"}}>Titre</th>
                                <th>Status</th>
                                <th>Intervenants</th>
                                <th width="100">Date limite</th>
                                <th width="100">Type</th>
                                <th width="150" style={{borderRadius: "0 0.5rem 0 0"}}>
                                    {<ClasseursButtonList
                                        classeurs={classeurs.filter(classeur => classeur.checked)}
                                        validClasseur={this.validClasseurs}
                                        revertClasseur={this.revertClasseurs}
                                        refuseClasseur={this.refuseClasseurs}
                                        removeClasseur={this.removeClasseurs}
                                        deleteClasseur={this.deleteClasseurs}
                                        signClasseur={this.signClasseurs}
                                        check={this.checkAllClasseurs}
                                        checked={checkedAll}
                                        id={"button-lists-large"}
                                        user={this.props.user}
                                        style={{fontSize: '0.8em'}}/>}
                                </th>
                            </tr>
                        </thead>
                        <tbody id="classeurRow">
                            {this.state.message === null ?
                                listClasseur :
                                <tr>
                                    <td/>
                                    <td/>
                                    <td className="text-center">
                                        {this.state.message}
                                    </td>
                                    <td/>
                                    <td/>
                                    <td/>
                                </tr>}
                        </tbody>
                        {classeurs &&
                            <tfoot>
                                <tr>
                                    <td>
                                        <Select
                                            id="limit"
                                            style={{
                                                height: '80%',
                                                margin: '0',
                                                paddingBottom: '0',
                                                paddingTop: '0',
                                                width: '60px'
                                            }}
                                            value={this.state.limit}
                                            className="cell small-4 medium-2 large-2"
                                            onChange={this.changeLimit}
                                            children={listLimit}/>
                                    </td>
                                    <td/>
                                    <td/>
                                    <td/>
                                    <td/>
                                    <td >
                                        <ClasseurPagination
                                            limit={limit}
                                            start={start}
                                            nbElement={this.state.nbElement}
                                            nbElementTotal={this.state.nbElementTotal}
                                            currentOrgId={this.context.user.current_org_id}
                                            changeLimit={this.changeLimit}
                                            changePreviousPage={this.changePreviousPage}
                                            changeNextPage={this.changeNextPage}
                                            changePage={this.changePage}
                                            url={this.props.url}/>
                                    </td>
                                </tr>
                            </tfoot>}
                        </table>
                    </div>
                </div>
        )
    }
}

Classeurs.PropTypes = {
    url: string.isRequired
}

export default translate(['sesile'])(Classeurs)


const ClasseurStateCaption = ({state, color = 'blue'}, {t}) =>
    <div
        className="align-middle"
        style={{
            marginRight: '10px',
            display: 'flex',
            textTransform: 'none',
            fontSize: '.8em',
            color: '#4a4c63'}}>
        <div
            style={{
                display: 'inline-block',
                width: '15px',
                height: '15px',
                borderRadius: '10px',
                background: `${color}`}}>
        </div>
        <span
            style={{
                marginLeft: '5px',
                display: 'inline-block'}}>
            {t(`common.classeurs.status.${state}`)}
        </span>
    </div>

ClasseurStateCaption.contextTypes = {
    t: func
}


const ListClasseur = ({classeurs}, {t}) => {

    return (
        <table className="hover">
            <thead>
            <tr>
                <th width="350">Titre</th>
                <th>Status</th>
                <th>Intervenants</th>
                <th width="100">Date limite</th>
                <th width="100">Type</th>
                <th width="150">Actions</th>
            </tr>
            </thead>
            {listClasseur.length > 0 ?
                <tbody>
                {listClasseur}
                </tbody> :
                <tfoot>
                <tr>
                    <td/>
                    <td className="text-center">{"Aucun classeur à afficher"}</td>
                    <td/>
                    <td/>
                    <td/>
                    <td/>
                </tr>
                </tfoot>}
        </table>
    )
}

ListClasseur.contextTypes = {
    t: func
}

const Intervenants = ({classeur}) => {
    const validEtape = classeur.etape_classeurs.find(etape_classeur => etape_classeur.etape_validante)
    return (
        <div>{
            validEtape ?
                validEtape.users.map(user =>
                        <span
                            key={`${user._nom}-${user.id}`}
                            style={{display: 'inline-block', width: '100%'}}>
                    {user._prenom + " " + user._nom}
                </span>
                ).concat(validEtape.user_packs.map(user_pack =>
                    <span
                        key={`${user_pack._nom}-${user_pack.id}`}
                        style={{display: 'inline-block', width: '100%'}}>
                        {user_pack.nom}
                    </span>)) :
                `${classeur.user._prenom} ${classeur.user._nom}`
        }
        </div>
    )
}