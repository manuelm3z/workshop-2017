import React, {
	Component
} from 'react';
import {
	connect
} from 'react-redux';
import {
	bindActionCreators
} from 'redux';
import {
	ActionCreators
} from '../actions';
import HomeContainer from './HomeContainer';

class AppContainer extends Component {
	addRecipe() {
		this.props.addRecipe();
	}

	render() {
		return (
			<HomeContainer {...this.props}/>
		);
	}	
}

function mapDispatchToProps(dispatch) {
	return bindActionCreators(ActionCreators, dispatch);
}

export default connect(state => {
	return {
		...state
	};
}, mapDispatchToProps)(AppContainer);