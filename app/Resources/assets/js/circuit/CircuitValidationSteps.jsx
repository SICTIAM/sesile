import React, {Component} from "react"
import {func, array} from "prop-types"
import {SortableContainer, SortableElement} from "react-sortable-hoc"
import { GridX, Cell } from '../_components/UI'
import SearchUserAndGroup from '../_components/SearchUserAndGroup'
import { StepItem } from '../_components/AdminUI'
import { translate } from 'react-i18next'

class CircuitValidationSteps extends Component {

    static contextTypes = {
        t: func
    }

    render() {

        const { t } = this.context
        const { steps, collectiviteId, onSortEnd, handleClickDeleteUser, handleClickDeleteGroup, handleClickDeleteStep, handleClickAddStep, addUser, addGroup } = this.props

        return (
            <CircuitValidationStepList  axis="x"
                                        pressDelay={200}
                                        pressThreshold={15}
                                        steps={steps}
                                        collectiviteId={collectiviteId}
                                        onSortEnd={onSortEnd}
                                        handleClickDeleteUser={handleClickDeleteUser}
                                        handleClickDeleteGroup={handleClickDeleteGroup}
                                        handleClickDeleteStep={handleClickDeleteStep}
                                        handleClickAddStep={handleClickAddStep}
                                        addUser={addUser}
                                        addGroup={addGroup}
                                        labelButtonAddStep={t('admin.circuit.add_step')}
            />
        )
    }
}

CircuitValidationSteps.propTypes = {
    steps: array.isRequired,
    onSortEnd: func.isRequired,
    handleClickDeleteUser: func.isRequired,
    handleClickDeleteGroup: func.isRequired,
    handleClickDeleteStep: func.isRequired,
    handleClickAddStep: func.isRequired,
    addUser: func.isRequired,
    addGroup: func.isRequired
}

export default translate('sesile')(CircuitValidationSteps)


const CircuitValidationStepList = SortableContainer(({steps, collectiviteId, handleClickDeleteUser, handleClickDeleteGroup, handleClickDeleteStep, handleClickAddStep, addGroup, addUser, labelButtonAddStep}) => {
    const listStep = steps.map((step, key) => <SortableCircuitValidationStep    key={`item-${key}`}
                                                                                index={key}
                                                                                stepKey={key}
                                                                                step={step}
                                                                                collectiviteId={collectiviteId}
                                                                                handleClickDeleteUser={handleClickDeleteUser}
                                                                                handleClickDeleteGroup={handleClickDeleteGroup}
                                                                                handleClickDeleteStep={handleClickDeleteStep}
                                                                                addUser={addUser}
                                                                                addGroup={addGroup} />)

    return (
        <GridX className="grid-margin-x grid-margin-y">
            {listStep}
            <Cell className="medium-3">
                <GridX className="step-item">
                    <button className="btn-add" type={"button"} onClick={() => handleClickAddStep()}>{labelButtonAddStep}</button>
                </GridX>
            </Cell>
        </GridX>
    )
})


const SortableCircuitValidationStep = SortableElement(({stepKey, step, collectiviteId, handleClickDeleteUser, handleClickDeleteGroup, handleClickDeleteStep, addGroup, addUser}) => {
    return (
        <CircuitValidationStep  key={stepKey}
                                stepKey={stepKey}
                                collectiviteId={collectiviteId}
                                step={step}
                                handleClickDeleteUser={handleClickDeleteUser}
                                handleClickDeleteGroup={handleClickDeleteGroup}
                                handleClickDeleteStep={handleClickDeleteStep}
                                addUser={addUser}
                                addGroup={addGroup}/>
    )
})

class CircuitValidationStep extends Component {

    static contextTypes = {t: func}

    static defaultProps = {step : {user_packs: [], users: []}}

    state = {inputDisplayed: false}

    render() {
        const { t } = this.context
        const { stepKey, step, collectiviteId, handleClickDeleteUser, handleClickDeleteGroup, handleClickDeleteStep, addGroup, addUser } = this.props
        const listUsers = step.users && step.users.map((user, key) => <li key={key}>{user._prenom + " " + user._nom}<a onClick={e => handleClickDeleteUser(stepKey, key)}>x</a></li>)
        const listGroups = step.user_packs && step.user_packs.map((group, key) => <li key={key}>{group.nom}<a onClick={e => handleClickDeleteGroup(stepKey, key)}>x</a></li>)

        return (
            <StepItem   stepKey={stepKey}
                        className="cell medium-3"
                        handleClickDeleteStep={handleClickDeleteStep}
                        title={t('admin.circuit.validat_step', {ordre:stepKey + 1})}>
                <ul className="no-bullet">
                    {listUsers && listUsers.length > 0 && <li><strong className="text-uppercase">{t('admin.user.name', {count: listUsers.length})}</strong></li>}
                    {listUsers}
                    {listGroups && listGroups.length > 0 && <li><strong className="text-uppercase">{t('admin.group.name', {count: listGroups.length})}</strong></li>}
                    {listGroups}
                    {this.state.inputDisplayed ?
                        <SearchUserAndGroup placeholder={t('admin.placeholder.type_userName_or_groupName')} addGroup={addGroup} addUser={addUser} stepKey={stepKey} step={step} collectiviteId={collectiviteId} /> :
                        <li><button className="btn-add" type={"button"} onClick={() => this.setState({inputDisplayed: true})}>{t('common.button.add_user')}</button></li>
                    }
                </ul>
            </StepItem>
        )
    }
}