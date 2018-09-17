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
    checkAllClasseurs() {
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
        const classeurRowList = this.state.classeurs.map(classeur =>
                <ClasseursRow
                    key={classeur.id}
                    classeur={classeur}
                    checkClasseur={this.checkClasseur}
                    validClasseur={this.validClasseurs}
                    revertClasseur={this.revertClasseurs}
                    refuseClasseur={this.refuseClasseurs}
                    removeClasseur={this.removeClasseurs}
                    deleteClasseur={this.deleteClasseurs}
                    signClasseur={this.signClasseurs}
                    user={this.context.user}/>)
        const listLimit = limits.map(limit =>
            <option key={limit} value={limit}>
                {limit}
            </option>)
        const classeursStatesCaption =
            this.statesCaption.map(stateCaption =>
                <ClasseurStateCaption key={stateCaption.state} state={stateCaption.state} color={stateCaption.color}/>)
        return (
            <div className="cell medium-12 head-list-classeurs">
                {(checkedAll || classeurs && classeurs.filter(classeur => classeur.checked).length > 1) &&
                    <div className="hide-for-large grid-x align-center-middle grid-padding-y">
                        <div className="cell medium-8">
                            <div className="grid-x panel grid-padding-y">
                                <div className="cell medium-12 classeur-button-list">
                                    <ClasseursButtonList
                                        key={0}
                                        classeurs={classeurs.filter(classeur => classeur.checked)}
                                        validClasseur={this.validClasseurs}
                                        revertClasseur={this.revertClasseurs}
                                        refuseClasseur={this.refuseClasseurs}
                                        removeClasseur={this.removeClasseurs}
                                        deleteClasseur={this.deleteClasseurs}
                                        signClasseur={this.signClasseurs}
                                        id={"button-lists-small"}
                                        user={this.context.user}/>
                                </div>
                            </div>
                        </div>
                    </div>}
                <div className="grid-x panel align-middle">
                    <div className="cell medium-12 list-classeurs">
                        <div
                            style={{
                                paddingTop: 0,
                                paddingBottom: 0,
                                fontSize: '.79em'
                            }}
                            className="grid-x panel-heading align-middle text-center tri-classeurs">
                            <div
                                style={{paddingLeft: '10px'}}
                                className="cell small-9 medium-8 large-6 text-left">
                                {t('common.classeurs.sort_label.name')}
                            </div>
                            <div className="cell large-2 show-for-large">
                                {t('common.stakeholders')}
                            </div>
                            <div className="cell small-3 medium-2 large-1">
                                {t('common.classeurs.sort_label.limit_date')}
                            </div>
                            <div className="cell large-1 show-for-large">
                                {t('common.classeurs.sort_label.type')}
                            </div>
                            <div className="cell large-2 show-for-large">
                                {<ClasseursButtonList
                                    key={1}
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
                                    user={this.context.user}
                                    style={{fontSize: '0.8em'}}/>}
                            </div>
                        </div>
                        <div id="classeurRow">
                            {this.state.message === null ?
                                classeurRowList :
                                <div className="grid-x panel-body align-middle align-center" style={{minHeight: '5em'}}>
                                    {this.state.message}
                                </div>}
                        </div>
                        {classeurs &&
                            <div
                                className="grid-x align-middle panel-heading"
                                style={{borderTop: 'solid 1px #a7a7a7', padding: '5px 10px'}}>
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
                                <div
                                    style={{display: '-webkit-inline-box'}}
                                    className="cell small-4 medium-7 large-7">
                                    {classeursStatesCaption}
                                </div>
                                <div
                                    className="cell small-4 medium-3 large-3"
                                    style={{height: '2.3em'}}>
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
                                </div>
                            </div>}
                    </div>
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